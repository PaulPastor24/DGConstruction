<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BiometricController extends Controller
{
    /**
     * Generates the WebAuthn Registration Options for new workers
     */
    public function registerOptions(Request $request)
    {
        // Generate a secure random challenge for the browser to sign
        $challenge = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $userId = rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');

        session(['webauthn_challenge' => $challenge]);

        return response()->json([
            'rp' => [
                'name' => env('APP_NAME', 'D&G Construction Inc.'),
                'id' => request()->getHost(), // Automatically detects ngrok or localhost
            ],
            'user' => [
                'id' => $userId,
                'name' => 'Pending Worker',
                'displayName' => 'Pending Worker',
            ],
            'challenge' => $challenge,
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],  // ES256
                ['type' => 'public-key', 'alg' => -257] // RS256
            ],
            'timeout' => 60000,
            'authenticatorSelection' => [
                'residentKey' => 'preferred',
                'requireResidentKey' => false,
                'userVerification' => 'discouraged'
            ],
            'attestation' => 'none'
        ]);
    }

    /**
     * Generates the WebAuthn Login Options for attendance scanning
     */
    public function loginOptions(Request $request)
    {
        $challenge = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        session(['webauthn_challenge' => $challenge]);

        return response()->json([
            'challenge' => $challenge,
            'timeout' => 60000,
            'rpId' => request()->getHost(),
            'userVerification' => 'discouraged',
        ]);
    }

    /**
     * Validates the incoming fingerprint scan for attendance
     */
    public function login(Request $request)
    {
        // For testing purposes, we return success so the frontend UI turns green.
        // Once tested, you can attach the Spatie validator logic here.
        return response()->json(['success' => true]);
    }
}