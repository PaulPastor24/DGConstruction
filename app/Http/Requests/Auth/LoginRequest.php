<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $rawRole = trim(strtolower($this->input('role')));
        $inputRole = match($rawRole) {
            'engineer' => 'engineer',
            'supervisor', 'site supervisor', 'site_supervisor' => 'supervisor',
            'client' => 'client',
            default => $rawRole,
        };

        $user = User::query()
            ->where('email', $this->input('email'))
            ->where('role', $inputRole)
            ->first();

        // 4. Fallback check: If matching by role fails, try matching by email only 
        // to see if the account actually exists in the database
        if (!$user) {
            $userByEmail = User::query()->where('email', '=', $this->input('email'))->first();
            
            if ($userByEmail) {
                // If the user exists but the role mismatched, throw an informative error
                throw ValidationException::withMessages([
                    'email' => "Account found, but role mismatch. Form sent: '{$this->input('role')}', Database expects: '{$userByEmail->role}'.",
                ]);
            }
        }

        // 5. Verify password against your custom column
        $storedHash = $user ? ($user->password ?? null) : null;

        if (!$user || !$storedHash || !Hash::check($this->input('password'), $storedHash)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // 6. Log the user in
        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}