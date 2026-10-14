<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\FaceAttendanceAwsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use RuntimeException;

class FaceAttendanceController extends Controller
{
    private function enabled(): void
    {
        abort_unless(config('face_attendance.enabled'), 503, 'Face attendance is not enabled.');
    }

    private function assignedWorker(int $workerId): object
    {
        $worker = DB::table('workers')->where('worker_id', $workerId)->where('is_active', 1)->first();
        abort_unless($worker, 404, 'Active worker not found.');
        $authorized = Project::query()
            ->whereHas('supervisors', fn ($q) => $q->where('supervisor_id', Auth::id()))
            ->whereHas('projectWorkers', fn ($q) => $q->where('worker_id', $workerId)->where('is_active', 1))
            ->exists();
        abort_unless($authorized, 403, 'Worker is not assigned to one of your active projects.');
        return $worker;
    }

    private function profile(object $worker): array
    {
        return [
            'worker_id' => (int) $worker->worker_id,
            'first_name' => (string) $worker->first_name,
            'last_name' => (string) $worker->last_name,
            'trade' => (string) ($worker->trade ?? 'General'),
            'profile_image_url' => !empty($worker->profile_image)
                ? asset('storage/'.ltrim($worker->profile_image, '/')) : null,
        ];
    }

    public function index()
    {
        $this->enabled();
        $assigned = Project::query()
            ->whereHas('supervisors', fn ($q) => $q->where('supervisor_id', Auth::id()))
            ->with('projectWorkers.worker')->get();
        $workers = $assigned->flatMap(fn ($p) => $p->projectWorkers)
            ->filter(fn ($d) => (int) ($d->is_active ?? 1) === 1 && $d->worker && (int) $d->worker->is_active === 1)
            ->map(fn ($d) => $d->worker)
            ->unique('worker_id')
            ->sortBy(fn ($w) => ($w->last_name ?? '').' '.($w->first_name ?? ''))
            ->values();
        $enrolledIds = DB::table('worker_face_enrollments')->whereIn('worker_id', $workers->pluck('worker_id'))
            ->pluck('worker_id')->map(fn ($i) => (int) $i)->all();

        return view('supervisor.face-attendance', [
            'workers' => $workers,
            'enrolledIds' => $enrolledIds,
            'faceWorkerChoices' => $workers->map(fn ($w) => [
                'worker_id' => (int) $w->worker_id,
                'name' => trim($w->first_name.' '.$w->last_name),
                'enrolled' => in_array((int) $w->worker_id, $enrolledIds, true),
            ])->values()->all(),
            'region' => config('face_attendance.region'),
        ]);
    }

    public function start(Request $request, FaceAttendanceAwsService $aws)
    {
        $this->enabled();
        $data = $request->validate([
            'purpose' => ['required', Rule::in(['enroll', 'attendance'])],
            'worker_id' => ['required_if:purpose,enroll', 'nullable', 'integer'],
            'supervisor_confirmed_identity' => ['required_if:purpose,enroll', 'nullable', 'accepted'],
            'privacy_notice_acknowledged' => ['required_if:purpose,enroll', 'nullable', 'accepted'],
        ]);
        if ($data['purpose'] === 'enroll') {
            $this->assignedWorker((int) $data['worker_id']);
        } elseif (!DB::table('worker_face_enrollments')->exists()) {
            return response()->json(['message' => 'Enroll a worker face first.'], 409);
        }

        // Disallow parallel scan sessions for one supervisor to prevent proof swapping.
        $pending = DB::table('worker_face_attempts')->where('supervisor_id', Auth::id())
            ->where('status', 'pending')->where('expires_at', '>', now())->exists();
        if ($pending) {
            return response()->json(['message' => 'Finish or cancel the existing scan before starting another.'], 409);
        }

        $request->session()->forget('worker_attendance_face_proof');
        try {
            $id = $aws->createLivenessSession();
            $creds = $aws->shortLivedStreamingCredentials();
            DB::table('worker_face_attempts')->insert([
                'aws_session_id' => $id,
                'supervisor_id' => Auth::id(),
                'worker_id' => $data['purpose'] === 'enroll' ? (int) $data['worker_id'] : null,
                'purpose' => $data['purpose'],
                'status' => 'pending',
                'expires_at' => now()->addMinutes(5),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            return response()->json([
                'session_id' => $id,
                'region' => config('face_attendance.region'),
                'credentials' => $creds,
            ])->header('Cache-Control', 'no-store, private');
        } catch (\Throwable $e) {
            Log::error('Could not start worker face scan', ['error_type' => get_class($e), 'message' => $e->getMessage()]);
            return response()->json(['message' => 'Face scan could not start. Check the server AWS configuration.'], 503);
        }
    }

    public function cancel(Request $request)
    {
        $this->enabled();
        $request->validate(['session_id' => ['nullable', 'uuid']]);
        if ($request->filled('session_id')) {
            DB::table('worker_face_attempts')->where('aws_session_id', $request->input('session_id'))
                ->where('supervisor_id', Auth::id())->where('status', 'pending')
                ->update(['status' => 'cancelled', 'updated_at' => now()]);
        }
        $request->session()->forget('worker_attendance_face_proof');
        return response()->json(['success' => true]);
    }

    public function finish(Request $request, FaceAttendanceAwsService $aws)
    {
        $this->enabled();
        $data = $request->validate(['session_id' => ['required', 'uuid']]);
        $attempt = DB::table('worker_face_attempts')
            ->where('aws_session_id', $data['session_id'])
            ->where('supervisor_id', Auth::id())
            ->first();

        if (!$attempt || $attempt->status !== 'pending' || now()->greaterThan($attempt->expires_at)) {
            return response()->json(['message' => 'The scan expired or has already been processed. Start again.'], 409);
        }
        // Atomically claim a one-use AWS session before contacting Rekognition.
        $claimed = DB::table('worker_face_attempts')->where('id', $attempt->id)
            ->where('status', 'pending')->update(['status' => 'processing', 'updated_at' => now()]);
        if (!$claimed) {
            return response()->json(['message' => 'Scan is already being processed.'], 409);
        }

        $newFaceId = null;
        try {
            $result = $aws->completedLiveness($attempt->aws_session_id);
            $matches = $aws->search($result['image']);
            $liveness = $result['confidence'];

            if ($attempt->purpose === 'enroll') {
                $worker = $this->assignedWorker((int) $attempt->worker_id);
                // Fail closed on any match belonging to another worker or unknown local face.
                if ($matches) {
                    $faceRows = DB::table('worker_face_enrollments')
                        ->whereIn('aws_face_id', array_column($matches, 'face_id'))->get()->keyBy('aws_face_id');
                    foreach ($matches as $match) {
                        $owner = $faceRows->get($match['face_id']);
                        if (!$owner || (int) $owner->worker_id !== (int) $worker->worker_id) {
                            throw new RuntimeException('This face may already be enrolled for another worker. Request manual review.');
                        }
                    }
                }

                $newFaceId = $aws->index($result['image'], (int) $worker->worker_id);
                $oldFaceId = DB::transaction(function () use ($worker, $newFaceId, $attempt, $liveness) {
                    $existing = DB::table('worker_face_enrollments')
                        ->where('worker_id', $worker->worker_id)->lockForUpdate()->first();
                    DB::table('worker_face_enrollments')->updateOrInsert(
                        ['worker_id' => $worker->worker_id],
                        ['aws_face_id' => $newFaceId, 'enrolled_by' => Auth::id(),
                         'enrolled_at' => now(), 'notice_acknowledged_at' => now(),
                         'privacy_notice_version' => config('face_attendance.privacy_notice_version'),
                         'updated_at' => now(), 'created_at' => $existing->created_at ?? now()]
                    );
                    $this->markAttempt((int) $attempt->id, 'enrolled', $liveness, null, $worker->worker_id);
                    return $existing->aws_face_id ?? null;
                });
                // AWS FaceId and database mapping are now committed together with the attempt.
                // Do not delete the new face if optional old-face cleanup fails afterward.
                $newFaceId = null;
                if ($oldFaceId) {
                    try { $aws->deleteFace($oldFaceId); }
                    catch (\Throwable $e) { Log::warning('Old face index cleanup needed', ['worker_id' => $worker->worker_id]); }
                }
                return response()->json(['success' => true, 'purpose' => 'enroll', 'worker' => $this->profile($worker)]);
            }

            if (!$matches) {
                throw new RuntimeException('Face not recognized. Ask the supervisor to use the authorized Manual Log.');
            }
            $faceRows = DB::table('worker_face_enrollments')
                ->whereIn('aws_face_id', array_column($matches, 'face_id'))->get()->keyBy('aws_face_id');
            $top = $matches[0];
            $owner = $faceRows->get($top['face_id']);
            if (!$owner) {
                throw new RuntimeException('Face collection is out of sync with workers. Request administrator review.');
            }
            foreach (array_slice($matches, 1) as $alternative) {
                $alternativeOwner = $faceRows->get($alternative['face_id']);
                // Unknown faces, or near-tied different workers, are not a safe identification.
                if (!$alternativeOwner || ((int) $alternativeOwner->worker_id !== (int) $owner->worker_id
                    && ($top['similarity'] - $alternative['similarity']) < (float) config('face_attendance.ambiguity_margin'))) {
                    throw new RuntimeException('Possible multiple identity match. Require supervisor review.');
                }
            }
            $worker = $this->assignedWorker((int) $owner->worker_id);
            $hasWorkerId = Schema::hasColumn('attendance_logs', 'worker_id');
            $duplicateQuery = DB::table('attendance_logs')
                ->whereDate('log_date', now('Asia/Manila')->toDateString());
            if ($hasWorkerId) {
                $duplicateQuery->where('worker_id', $worker->worker_id);
            } else {
                $deploymentIds = DB::table('project_workers')
                    ->where('worker_id', $worker->worker_id)
                    ->pluck('deployment_id');
                $duplicateQuery->whereIn('deployment_id', $deploymentIds);
            }
            if ($duplicateQuery->exists()) {
                throw new RuntimeException('This worker already has a Time In record today.');
            }

            $this->markAttempt((int) $attempt->id, 'matched', $liveness, $top['similarity'], $worker->worker_id);
            $request->session()->put('worker_attendance_face_proof', [
                'worker_id' => (int) $worker->worker_id,
                'supervisor_id' => (int) Auth::id(),
                'verified_at' => now()->timestamp,
                'attempt_id' => (int) $attempt->id,
            ]);
            return response()->json([
                'success' => true, 'purpose' => 'attendance',
                'worker' => $this->profile($worker),
                'match_score' => $top['similarity'],
                'liveness_score' => $liveness,
                'needs_supervisor_confirmation' => true,
            ])->header('Cache-Control', 'no-store, private');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            $this->markAttempt((int) $attempt->id, 'rejected');
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (RuntimeException $e) {
            if ($newFaceId) { try { $aws->deleteFace($newFaceId); } catch (\Throwable) {} }
            // AWS SDK and transport errors must not be exposed verbatim to browsers.
            if ($e instanceof \Aws\Exception\AwsException) {
                $this->markAttempt((int) $attempt->id, 'failed');
                Log::error('AWS face operation failed', ['code' => $e->getAwsErrorCode()]);
                return response()->json(['message' => 'Cloud facial recognition is temporarily unavailable. Use authorized Manual Log.'], 503);
            }
            $this->markAttempt((int) $attempt->id, 'rejected');
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            if ($newFaceId) { try { $aws->deleteFace($newFaceId); } catch (\Throwable) {} }
            $this->markAttempt((int) $attempt->id, 'failed');
            Log::error('Face attendance verification failed', ['exception' => get_class($e), 'message' => $e->getMessage()]);
            return response()->json(['message' => 'Face verification failed. Retry or use authorized Manual Log.'], 503);
        }
    }

    private function markAttempt(int $id, string $status, ?float $liveness = null, ?float $similarity = null, ?int $worker = null): void
    {
        DB::table('worker_face_attempts')->where('id', $id)->update([
            'status' => $status, 'liveness_score' => $liveness, 'match_score' => $similarity,
            'matched_worker_id' => $worker, 'used_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function removeEnrollment(Request $request, int $workerId, FaceAttendanceAwsService $aws)
    {
        $this->enabled();
        $this->assignedWorker($workerId);
        $request->validate(['confirm' => ['required', 'accepted']]);
        $row = DB::table('worker_face_enrollments')->where('worker_id', $workerId)->first();
        if (!$row) { return response()->json(['message' => 'No face enrollment exists.'], 404); }
        // Delete the biometric face record in AWS before removing local mapping.
        $aws->deleteFace($row->aws_face_id);
        DB::table('worker_face_enrollments')->where('worker_id', $workerId)->delete();
        return response()->json(['success' => true]);
    }
}
