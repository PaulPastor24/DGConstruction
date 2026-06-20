<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        
        $total_users = DB::table('users')->count();
        $engineers_count = DB::table('users')->where('role', 'engineer')->count();
        $supervisors_count = DB::table('users')->where('role', 'site_supervisor')->count();
        $clients_count = DB::table('users')->where('role', 'client')->count();

        return view('admin.users.index', compact('users', 'total_users', 'engineers_count', 'supervisors_count', 'clients_count'));
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

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
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

            $originalData = [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'contact_number' => $user->contact_number,
                'is_active' => (int) $user->is_active,
            ];

            $newData = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'role' => $request->input('role'),
                'contact_number' => $request->input('contact_number'),
                'is_active' => (int) $request->input('is_active', $user->is_active),
            ];

            if ($originalData == $newData && !$request->filled('password')) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('info', 'No changes were detected.');
            }

            $data = $newData;

            // Only update password if provided
            if ($request->filled('password')) {
                $data['password_hash'] = Hash::make($request->password);
            }

            $user->update($data);

            // If role changed to 'client' and client profile doesn't exist, create it
            if ($newData['role'] === 'client' && !$user->client) {
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

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        try {
            $hasRelatedRecords = (
                $user->engineeredProjects()->exists() ||
                $user->supervisedProjects()->exists() ||
                $user->client()->exists() ||
                $user->attendanceLogs()->exists() ||
                $user->submittedReports()->exists()
            );

            if ($hasRelatedRecords) {
                return redirect()
                    ->back()
                    ->with('error', 'This user cannot be deleted because they are assigned to or related to existing records.');
            }

            DB::table('users')
                ->where('user_id', $user->user_id)
                ->delete();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User deleted successfully!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}