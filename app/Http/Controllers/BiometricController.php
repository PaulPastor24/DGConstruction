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

    public function registerWorkerBiometric(Request $request, WorkerPasskeyService $workerPasskeys)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'trade' => ['nullable', 'string', 'max:100'],
            'credential' => ['required', 'array'],
        ]);

        // Never store the raw browser credential as proof of enrollment.
        // Registration must be validated against the challenge and origin
        // before saving the server-side credential public key.
        try {
            $credentialSource = $workerPasskeys->verifyRegistration($validated['credential']);
            $credentialId = $workerPasskeys->credentialId($credentialSource);
            $credentialJson = $workerPasskeys->serializeCredentialSource($credentialSource);
        } catch (\Throwable $error) {
            Log::warning('Worker biometric registration verification failed.', [
                'error' => $error->getMessage(),
                'exception' => get_class($error),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ]);
            return response()->json([
                'message' => 'Worker passkey enrollment could not be verified. Please start enrollment again.',
            ], 422);
        }

        if (DB::table('workers')->where('credential_id', $credentialId)->exists()) {
            return response()->json([
                'message' => 'This passkey is already registered to a worker.',
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
                'credential_json' => $credentialJson,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'worker_id');

            $worker = DB::table('workers')->where('worker_id', $workerId)->first();
            return response()->json([
                'message' => 'Worker passkey successfully registered.',
                'worker' => $this->normalizeWorkerResponse($worker),
            ]);
        } catch (\Throwable $error) {
            Log::error('Failed to register biometric worker', [
                'error' => $error->getMessage(),
            ]);
            return response()->json([
                'message' => 'Failed to save worker.',
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
                'exception' => get_class($error),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ]);

            $errorMessage = strtolower($error->getMessage());
            $requiresReenrollment = str_contains($errorMessage, 'rp id')
                || str_contains($errorMessage, 'relying party')
                || str_contains($errorMessage, 'origin')
                || str_contains($errorMessage, 'credential source')
                || str_contains($errorMessage, 'does not match')
                || str_contains($errorMessage, 'needs to be enrolled')
                || str_contains($errorMessage, 're-enroll')
                || str_contains($errorMessage, 'passkey record');

            return response()->json([
                'message' => $requiresReenrollment
                    ? 'This worker passkey is invalid, incompatible, or enrolled for another site. Ask the worker to re-enroll on their own trusted device.'
                    : 'Biometric verification failed. Please try scanning again.',
                'requires_reenrollment' => $requiresReenrollment,
                'error_code' => $requiresReenrollment ? 'reenrollment_required' : 'verification_failed',
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