<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleClientController extends Controller
{
    private function googleProvider()
    {
        return Socialite::driver('google')
            ->redirectUrl(config('services.google.redirect'));
    }

    public function redirect()
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->with('error', 'Google sign-in is not configured. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to the .env file.');
        }

        return $this->googleProvider()
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('login')->with('error', 'Google sign-in was cancelled or denied.');
        }

        if (! $request->filled('code')) {
            return redirect()->route('login')->with('error', 'Google sign-in did not return an authorization code. Please try again.');
        }

        try {
            $googleUser = $this->googleProvider()->user();
        } catch (\Throwable $exception) {
            Log::warning('Google OAuth callback failed: '.$exception->getMessage());

            return redirect()->route('login')->with('error', 'Google sign-in could not be completed. Please try again.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user || $user->role !== 'client' || ! $user->is_active) {
            return redirect()->route('login')->with('error', 'Only registered active client accounts may use Google sign-in.');
        }

        $user->forceFill(['google_id' => $googleUser->getId()])->save();
        Auth::login($user, true);

        return redirect()->intended(route('client.dashboard'));
    }
}