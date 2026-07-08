<?php

namespace App\Http\Controllers;

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

    public function registerOptions(Request $request)
    {
        $firstName = trim($request->input('first_name', 'Pending'));
        $lastName = trim($request->input('last_name', 'Worker'));

        $displayName = trim($firstName . ' ' . $lastName);

        if ($displayName === '') {
            $displayName = 'Pending Worker';
        }

        $challenge = $this->base64UrlEncode(random_bytes(32));
        $userId = $this->base64UrlEncode(random_bytes(16));

        session([
            'webauthn_challenge' => $challenge,
            'webauthn_user_id' => $userId,
            'webauthn_pending_worker_name' => $displayName,
        ]);

        return response()->json([
            'rp' => [
                'name' => env('APP_NAME', 'D&G Construction Inc.'),
                'id' => request()->getHost(),
            ],
            'user' => [
                'id' => $userId,
                'name' => strtolower(str_replace(' ', '.', $displayName)) . '@workers.local',
                'displayName' => $displayName,
            ],
            'challenge' => $challenge,
            'pubKeyCredParams' => [
                [
                    'type' => 'public-key',
                    'alg' => -7,
                ],
                [
                    'type' => 'public-key',
                    'alg' => -257,
                ],
            ],
            'timeout' => 60000,
            'authenticatorSelection' => [
                'residentKey' => 'preferred',
                'requireResidentKey' => false,
                'userVerification' => 'discouraged',
            ],
            'attestation' => 'none',
        ]);
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

    public function loginOptions(Request $request)
    {
        $challenge = $this->base64UrlEncode(random_bytes(32));

        session([
            'webauthn_challenge' => $challenge,
        ]);

        $credentials = DB::table('workers')
            ->where('is_active', 1)
            ->whereNotNull('credential_id')
            ->pluck('credential_id')
            ->filter()
            ->values()
            ->map(function ($credentialId) {
                return [
                    'id' => $credentialId,
                    'type' => 'public-key',
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'challenge' => $challenge,
            'timeout' => 60000,
            'rpId' => request()->getHost(),
            'userVerification' => 'discouraged',
            'allowCredentials' => $credentials,
        ]);
    }

    public function login(Request $request)
    {
        $credentialId = $request->input('id') ?: $request->input('rawId');

        if (!$credentialId) {
            return response()->json([
                'message' => 'No credential ID received from biometric scan.',
            ], 422);
        }

        $worker = DB::table('workers')
            ->where('is_active', 1)
            ->where('credential_id', $credentialId)
            ->first();

        if (!$worker) {
            return response()->json([
                'message' => 'Authentication failed: Worker not recognized.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'worker' => $this->normalizeWorkerResponse($worker),
        ]);
    }
}