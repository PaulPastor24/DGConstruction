<?php

namespace App\Http\Controllers;

use App\Services\WorkerPasskeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiometricController extends Controller
{
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function normalizeWorkerResponse($worker): array
    {
        $worker = (array) $worker;

        return [
            'worker_id' => $worker['worker_id'] ?? null,
            'first_name' => $worker['first_name'] ?? '',
            'last_name' => $worker['last_name'] ?? '',
            'trade' => $worker['trade'] ?? 'General',
            'contact_number' => $worker['contact_number'] ?? null,
            'profile_image_url' => ! empty($worker['profile_image'])
                ? asset('storage/'.ltrim($worker['profile_image'], '/'))
                : null,
            'created_at' => $worker['created_at'] ?? now()->toDateTimeString(),
        ];
    }

    private function extractCredentialId(array $credential): ?string
    {
        $credentialId = $credential['id'] ?? $credential['rawId'] ?? null;

        if (!$credentialId || is_array($credentialId)) {
            return null;
        }

        return (string) $credentialId;
    }

    public function registerOptions(Request $request, WorkerPasskeyService $workerPasskeys)
    {
        $firstName = trim($request->input('first_name', 'Pending')) ?: 'Pending';
        $lastName = trim($request->input('last_name', 'Worker')) ?: 'Worker';

        return response()->json($workerPasskeys->createRegistrationOptions($firstName, $lastName));
    }

    public function registerWorkerBiometric(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'trade' => ['nullable', 'string', 'max:100'],
            'credential' => ['required', 'array'],
        ]);

        $credential = $validated['credential'];
        $credentialId = $this->extractCredentialId($credential);

        if (!$credentialId) {
            return response()->json([
                'message' => 'Credential ID was not received from the browser.',
            ], 422);
        }

        try {
            $workerId = DB::table('workers')->insertGetId([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'trade' => $validated['trade'] ?: 'General',
                'contact_number' => null,
                'is_active' => 1,
                'credential_id' => $credentialId,
                'credential_json' => json_encode($credential),
                'created_at' => now(),
                'updated_at' => now(),
            ], 'worker_id');

            $worker = DB::table('workers')
                ->where('worker_id', $workerId)
                ->first();

            return response()->json([
                'message' => 'Worker successfully registered.',
                'worker' => $this->normalizeWorkerResponse($worker),
            ]);
        } catch (\Throwable $error) {
            Log::error('Failed to register biometric worker', [
                'error' => $error->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to save worker.',
                'error' => $error->getMessage(),
            ], 500);
        }
    }

    public function listWorkers(Request $request)
    {
        $workers = DB::table('workers')
            ->where('is_active', 1)
            ->orderByDesc('created_at')
            ->paginate(10);

        $workers->getCollection()->transform(function ($worker) {
            return $this->normalizeWorkerResponse($worker);
        });

        return response()->json($workers);
    }

    public function loginOptions(Request $request, WorkerPasskeyService $workerPasskeys)
    {
        $credentials = DB::table('workers')
            ->where('is_active', 1)
            ->whereNotNull('credential_id')
            ->whereNotNull('credential_json')
            ->get(['credential_id']);

        return response()->json($workerPasskeys->createAuthenticationOptions($credentials));
    }

    public function login(Request $request, WorkerPasskeyService $workerPasskeys)
    {
        $validated = $request->validate([
            'id' => ['required', 'string'],
            'rawId' => ['required', 'string'],
            'type' => ['required', 'in:public-key'],
            'response' => ['required', 'array'],
        ]);

        $worker = DB::table('workers')
            ->where('is_active', 1)
            ->where('credential_id', $validated['id'])
            ->whereNotNull('credential_json')
            ->first();

        if (!$worker) {
            return response()->json([
                'message' => 'Authentication failed: Worker not recognized.',
            ], 404);
        }

        try {
            $credentialSource = $workerPasskeys->verifyAssertion($request->all(), $worker);
        } catch (\Throwable $error) {
            Log::warning('Worker biometric assertion verification failed.', [
                'worker_id' => $worker->worker_id,
                'error' => $error->getMessage(),
            ]);

            $errorMessage = strtolower($error->getMessage());
            $requiresReenrollment = str_contains($errorMessage, 'rp id')
                || str_contains($errorMessage, 'relying party')
                || str_contains($errorMessage, 'origin')
                || str_contains($errorMessage, 'credential source');

            return response()->json([
                'message' => $requiresReenrollment
                    ? 'This biometric was enrolled on a different site address. Open Enrolled Workers, choose Edit, and initialize the fingerprint again.'
                    : 'Biometric verification failed. Please try scanning again.',
                'requires_reenrollment' => $requiresReenrollment,
            ], 422);
        }

        DB::table('workers')
            ->where('worker_id', $worker->worker_id)
            ->update([
                'credential_json' => $workerPasskeys->serializeCredentialSource($credentialSource),
                'updated_at' => now(),
            ]);

        $worker = DB::table('workers')->where('worker_id', $worker->worker_id)->first();

        return response()->json([
            'success' => true,
            'worker' => $this->normalizeWorkerResponse($worker),
        ]);
    }
}