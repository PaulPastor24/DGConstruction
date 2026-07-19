<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->role === 'engineer') return redirect()->intended(route('admin.dashboard'))->with('login_success', true);
        if ($user->role === 'supervisor') return redirect()->intended(route('supervisor.dashboard'))->with('login_success', true);
        if ($user->role === 'client') return redirect()->intended(route('client.dashboard'))->with('login_success', true);

        return redirect()->intended(url('/'))->with('login_success', true);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}