<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            // Create the user (split name into first/last for revised schema)
            $parts = preg_split('/\s+/', trim($request->name), 2);
            $first = $parts[0] ?? $request->name;
            $last = $parts[1] ?? '';

            $user = User::create([
                'first_name' => $first,
                'last_name' => $last,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'contact_number' => $request->contact_number,
                'is_active' => $request->is_active ?? true,
            ]);

            // If the user is a client, create a corresponding client profile
            if ($request->role === 'client') {
                Client::create([
                    'user_id' => $user->user_id,
                    'company_name' => null,
                    'address' => null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $parts = preg_split('/\s+/', trim($request->name), 2);
            $first = $parts[0] ?? $request->name;
            $last = $parts[1] ?? '';

            $data = [
                'first_name' => $first,
                'last_name' => $last,
                'email' => $request->email,
                'role' => $request->role,
                'contact_number' => $request->contact_number,
                'is_active' => $request->is_active ?? false,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // If role changed to 'client' and client profile doesn't exist, create it
            if ($data['role'] === 'client' && !$user->client) {
                Client::create([
                    'user_id' => $user->user_id,
                    'company_name' => null,
                    'address' => null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        try {
            $user->update(['is_active' => !$user->is_active]);

            $status = $user->is_active ? 'activated' : 'deactivated';

            return redirect()
                ->route('admin.users.index')
                ->with('success', "User {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update user status: ' . $e->getMessage());
        }
    }
}