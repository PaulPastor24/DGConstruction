<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\LocalFaceMatcher;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FaceAttendanceController extends Controller
{
    private function enabled(): void
    {
        abort_unless(config('face_attendance.enabled'), 503, 'Face attendance is not enabled.');
    }

    private function workerIds()
    {
        $projects = Project::query()->whereHas('supervisors', fn ($q) => $q
            ->where('supervisor_id', Auth::id())->where('project_supervisors.is_active', 1))
            ->pluck('project_id');

        return DB::table('project_workers')->whereIn('project_id', $projects)
            ->where('is_active', 1)->pluck('worker_id');
    }

    private function assignedWorker(int $id): object
    {
        abort_unless($this->workerIds()->contains($id), 403, 'Worker is not assigned to your projects.');

        return DB::table('workers')->where('worker_id', $id)->where('is_active', 1)->first()
            ?? abort(404, 'Active worker not found.');
    }

    private function profile(object $worker): array
    {
        return [
            'worker_id' => (int) $worker->worker_id,
            'first_name' => $worker->first_name,
            'last_name' => $worker->last_name,
            'trade' => $worker->trade ?? 'General',
            'profile_image_url' => ! empty($worker->profile_image)
                ? asset('storage/'.ltrim($worker->profile_image, '/')) : null,
        ];
    }

    public function index()
    {
        $this->enabled();
        $workers = DB::table('workers')->whereIn('worker_id', $this->workerIds())
            ->where('is_active', 1)->orderBy('last_name')->get();
        $enrolled = DB::table('worker_face_enrollments')->whereNotNull('face_descriptors')
            ->where('model_version', config('face_attendance.model_version'))->pluck('worker_id');

        return view('supervisor.face-attendance', [
            'faceWorkerChoices' => $workers->map(fn ($w) => [
                'worker_id' => $w->worker_id, 'name' => trim($w->first_name.' '.$w->last_name),
                'enrolled' => $enrolled->contains($w->worker_id),
            ])->values()->all(),
        ]);
    }

    public function start(Request $request)
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
        } elseif (! DB::table('worker_face_enrollments')->whereIn('worker_id', $this->workerIds())
            ->whereNotNull('face_descriptors')->where('model_version', config('face_attendance.model_version'))->exists()) {
            return response()->json(['message' => 'No saved face enrollment was found for your assigned workers. Choose Enroll worker face and select an existing worker. Enroll once; the saved face works on other phones.'], 409);
        }
        $request->session()->forget('worker_attendance_face_proof');
        // A new scan supersedes abandoned sessions (including a page refresh).
        DB::table('worker_face_attempts')->where('supervisor_id', Auth::id())->where('status', 'pending')
            ->update(['status' => 'cancelled', 'updated_at' => now()]);
        $id = (string) Str::uuid();
        DB::table('worker_face_attempts')->insert([
            // Keep the historical column name for compatibility with deployed databases.
            'aws_session_id' => $id, 'supervisor_id' => Auth::id(),
            'worker_id' => $data['purpose'] === 'enroll' ? $data['worker_id'] : null,
            'purpose' => $data['purpose'], 'status' => 'pending',
            'expires_at' => now()->addMinutes(5), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return response()->json(['session_id' => $id, 'model_version' => config('face_attendance.model_version')])
            ->header('Cache-Control', 'no-store, private');
    }

    public function cancel(Request $request)
    {
        $this->enabled();
        $data = $request->validate(['session_id' => ['nullable', 'uuid']]);
        DB::table('worker_face_attempts')->where('supervisor_id', Auth::id())->where('status', 'pending')
            ->when(! empty($data['session_id']), fn ($q) => $q->where('aws_session_id', $data['session_id']))
            ->update(['status' => 'cancelled', 'updated_at' => now()]);
        $request->session()->forget('worker_attendance_face_proof');

        return response()->json(['success' => true]);
    }

    public function finish(Request $request, LocalFaceMatcher $matcher)
    {
        $this->enabled();
        $request->session()->forget('worker_attendance_face_proof');
        $data = $request->validate([
            'session_id' => ['required', 'uuid'],
            'model_version' => ['required', Rule::in([config('face_attendance.model_version')])],
            'samples' => ['required', 'array', 'size:3'],
            'samples.*' => ['required', 'array', 'size:1024'],
            'samples.*.*' => ['required', 'numeric', 'between:-100,100'],
        ]);
        $attempt = DB::table('worker_face_attempts')->where('aws_session_id', $data['session_id'])
            ->where('supervisor_id', Auth::id())->first();
        if (! $attempt || $attempt->status !== 'pending' || now()->greaterThan($attempt->expires_at)) {
            return response()->json(['message' => 'Scan expired or already processed. Start again.'], 409);
        }
        $claimed = DB::table('worker_face_attempts')->where('id', $attempt->id)->where('status', 'pending')
            ->update(['status' => 'processing', 'updated_at' => now()]);
        if (! $claimed) {
            return response()->json(['message' => 'Scan already processed.'], 409);
        }
        try {
            $matcher->validateSamples($data['samples']);
            // Enrollment checks all local templates for duplicates. Templates never leave the server.
            $rows = DB::table('worker_face_enrollments')->whereNotNull('face_descriptors')
                ->where('model_version', config('face_attendance.model_version'))->get();
            $scores = [];
            foreach ($rows as $row) {
                $samples = json_decode(Crypt::decryptString($row->face_descriptors), true, 512, JSON_THROW_ON_ERROR);
                $matcher->validateSamples($samples);
                $scores[] = ['worker_id' => (int) $row->worker_id, 'score' => $matcher->score($data['samples'], $samples)];
            }
            usort($scores, fn ($a, $b) => $b['score'] <=> $a['score']);
            $threshold = (float) config('face_attendance.similarity_threshold');
            if ($attempt->purpose === 'enroll') {
                $worker = $this->assignedWorker((int) $attempt->worker_id);
                foreach ($scores as $candidate) {
                    if ($candidate['worker_id'] !== (int) $worker->worker_id && $candidate['score'] >= $threshold) {
                        throw new RuntimeException('This face may belong to another enrolled worker. Request supervisor review.');
                    }
                }
                DB::transaction(function () use ($worker, $data, $attempt) {
                    $old = DB::table('worker_face_enrollments')->where('worker_id', $worker->worker_id)->first();
                    DB::table('worker_face_enrollments')->updateOrInsert(['worker_id' => $worker->worker_id], [
                        'aws_face_id' => 'local:'.Str::uuid(),
                        'face_descriptors' => Crypt::encryptString(json_encode($data['samples'], JSON_THROW_ON_ERROR)),
                        'model_version' => config('face_attendance.model_version'),
                        'enrolled_by' => Auth::id(), 'enrolled_at' => now(), 'notice_acknowledged_at' => now(),
                        'privacy_notice_version' => config('face_attendance.privacy_notice_version'),
                        'created_at' => $old->created_at ?? now(), 'updated_at' => now(),
                    ]);
                    $this->markAttempt($attempt->id, 'enrolled', null, $worker->worker_id);
                });

                return response()->json(['success' => true, 'purpose' => 'enroll', 'worker' => $this->profile($worker)]);
            }
            $top = $scores[0] ?? null;
            if (! $top || $top['score'] < $threshold) {
                throw new RuntimeException('Face not recognized. Retry in good lighting or use Manual Log.');
            }
            if (isset($scores[1]) && config('face_attendance.ambiguity_margin') > $top['score'] - $scores[1]['score']) {
                throw new RuntimeException('Match is uncertain between workers. Rescan or use Manual Log.');
            }
            $worker = $this->assignedWorker($top['worker_id']);
            $this->markAttempt($attempt->id, 'matched', $top['score'], $worker->worker_id);
            $request->session()->put('worker_attendance_face_proof', [
                'worker_id' => (int) $worker->worker_id, 'supervisor_id' => (int) Auth::id(),
                'verified_at' => now()->timestamp, 'attempt_id' => (int) $attempt->id,
                'provider' => 'local_supervised',
            ]);

            return response()->json([
                'success' => true, 'purpose' => 'attendance', 'worker' => $this->profile($worker),
                'match_score' => $top['score'], 'needs_supervisor_confirmation' => true,
                'log_date' => now('Asia/Manila')->toDateString(),
            ])->header('Cache-Control', 'no-store, private');
        } catch (HttpExceptionInterface $e) {
            $this->markAttempt($attempt->id, 'rejected');

            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (DecryptException $e) {
            $this->markAttempt($attempt->id, 'failed');
            Log::error('Face template decryption failed. Check APP_KEY.');

            return response()->json(['message' => 'Stored face templates cannot be opened. Ask the administrator to check the application key.'], 503);
        } catch (RuntimeException $e) {
            $this->markAttempt($attempt->id, 'rejected');

            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $this->markAttempt($attempt->id, 'failed');
            Log::error('Local face scan failed', ['exception' => get_class($e)]);

            return response()->json(['message' => 'Face templates could not be processed. Ask the administrator to check the application key and database migrations.'], 503);
        }
    }

    private function markAttempt(int $id, string $status, ?float $score = null, ?int $worker = null): void
    {
        DB::table('worker_face_attempts')->where('id', $id)->update([
            'status' => $status, 'liveness_score' => null, 'match_score' => $score,
            'matched_worker_id' => $worker, 'used_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function removeEnrollment(Request $request, int $workerId)
    {
        $this->enabled();
        $this->assignedWorker($workerId);
        $request->validate(['confirm' => ['required', 'accepted']]);
        DB::table('worker_face_enrollments')->where('worker_id', $workerId)->delete();
        $request->session()->forget('worker_attendance_face_proof');

        return response()->json(['success' => true]);
    }
}
