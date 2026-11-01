<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $parts = preg_split('/\s+/', trim($data['name']), 2);
        $data['first_name'] = $parts[0];
        $data['last_name'] = $parts[1] ?? '';
        // The revised production schema stores names in separate columns.
        if (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'name')) {
            unset($data['name']);
        }
        $request->user()->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        abort_unless($user->canDeleteUsers(), 403, 'Only the designated administrator can delete user accounts.');
        throw ValidationException::withMessages([
            'account' => 'You cannot delete your own account while logged in.',
        ])->errorBag('userDeletion');
    }
}
