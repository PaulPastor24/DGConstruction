<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users with filters applied.
     */
    public function index(Request $request)
    {
        // Start building query, eager-loading the client relationship
        $query = User::with('client');

        // 1. Text Search Filter (first_name, last_name, email, and client company_name)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function ($q2) use ($search) {
                      $q2->where('company_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // 2. Role Filter
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // 3. Status Filter
        if ($request->filled('status')) {
            $status = $request->input('status') === 'active' ? 1 : 0;
            $query->where('is_active', $status);
        }

        // 4. Per Page Configuration
        $perPage = $request->input('per_page', 25);

        // Fetch paginated records ordered by recent
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Statistical Counts
        $total_users = DB::table('users')->count();
        $active_users_count = DB::table('users')->where('is_active', 1)->count();
        $inactive_users_count = DB::table('users')->where('is_active', 0)->count();
        $engineers_count = DB::table('users')->where('role', 'engineer')->count();
        $supervisors_count = DB::table('users')->where('role', 'supervisor')->count();
        $clients_count = DB::table('users')->where('role', 'client')->count();

        return view('admin.users.index', compact(
            'users',
            'total_users',
            'active_users_count',
            'inactive_users_count',
            'engineers_count',
            'supervisors_count',
            'clients_count'
        ));
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
        $isAjax = $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        // Idempotency guard: the form sends a fresh random key each time the
        // "Add User" modal is opened. If two requests arrive for the same
        // key (double-click, double-fired submit event, retried request,
        // etc.), only the first one is allowed to actually insert a row.
        // The second one waits for and replays the first one's result
        // instead of racing it and hitting a false "duplicate email" error.
        $idempotencyKey = $request->input('idempotency_key');
        $cacheKey = $idempotencyKey ? "user_store_idem_{$idempotencyKey}" : null;

        if ($cacheKey) {
            $claimed = Cache::add($cacheKey, ['status' => 'processing'], now()->addMinutes(2));

            if (!$claimed) {
                // Another request for this exact submission is already
                // running (or just finished). Wait briefly for its result.
                for ($i = 0; $i < 20; $i++) {
                    usleep(150000); // 150ms, ~3s total
                    $cached = Cache::get($cacheKey);
                    if (is_array($cached) && $cached['status'] === 'done') {
                        return $isAjax
                            ? response()->json($cached['payload'], $cached['code'])
                            : redirect()->route('admin.users.index')->with(
                                $cached['code'] < 400 ? 'success' : 'error',
                                $cached['payload']['message'] ?? ''
                            );
                    }
                }

                $message = 'This submission is already being processed. Please wait a moment.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $message], 409)
                    : redirect()->back()->withInput()->with('error', $message);
            }
        }

        $finish = function (int $code, array $payload) use ($cacheKey) {
            if ($cacheKey) {
                Cache::put($cacheKey, ['status' => 'done', 'code' => $code, 'payload' => $payload], now()->addMinutes(2));
            }
        };

        try {
            DB::beginTransaction();

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'contact_number' => $request->contact_number,
                'is_active' => true,
            ]);

            if ($request->role === 'client') {
                Client::create([
                    'user_id' => $user->user_id,
                    'company_name' => null,
                    'address' => null,
                ]);
            }

            DB::commit();

            $payload = [
                'success' => true,
                'message' => 'User created successfully!',
                'user'    => $user->fresh(),
            ];
            $finish(200, $payload);

            // Always return JSON for AJAX requests (indicated by X-Requested-With header)
            if ($isAjax) {
                return response()->json($payload, 200);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User created successfully!');

        } catch (QueryException $e) {
            DB::rollBack();

            // MySQL error code 1062 = duplicate entry (e.g. two rapid
            // submissions racing past validation before either row exists)
            if (($e->errorInfo[1] ?? null) == 1062) {
                $message = 'This email address is already registered.';
                $payload = ['success' => false, 'errors' => ['email' => [$message]], 'message' => $message];
                $finish(422, $payload);

                if ($isAjax) {
                    return response()->json($payload, 422);
                }

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', $message);
            }

            $message = 'Failed to create user due to a database error.';
            $payload = ['success' => false, 'message' => $message];
            $finish(500, $payload);

            if ($isAjax) {
                return response()->json($payload, 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            $message = 'Failed to create user: ' . $e->getMessage();
            $payload = ['success' => false, 'message' => $message];
            $finish(500, $payload);

            if ($isAjax) {
                return response()->json($payload, 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $message);
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
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'contact_number' => $user->contact_number,
                'is_active' => (int) $user->is_active,
            ];

            $newData = [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'email' => $request->input('email'),
                'role' => $request->input('role'),
                'contact_number' => $request->input('contact_number'),
                'is_active' => (int) $request->input('is_active', $user->is_active),
            ];

            if ($originalData == $newData && !$request->filled('password')) {
                DB::rollBack();

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'info'    => true,
                        'message' => 'No changes were detected.',
                    ], 200);
                }

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('info', 'No changes were detected.');
            }

            $data = $newData;

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            if ($newData['role'] === 'client' && !$user->client) {
                Client::create([
                    'user_id' => $user->user_id,
                    'company_name' => null,
                    'address' => null,
                ]);
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully!',
                    'user'    => $user->fresh(),
                ]);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User updated successfully!');

        } catch (QueryException $e) {
            DB::rollBack();

            // MySQL error code 1062 = duplicate entry (e.g. two rapid
            // submissions racing past validation before either row is saved)
            if (($e->errorInfo[1] ?? null) == 1062) {
                $message = 'This email address is already registered.';

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors'  => ['email' => [$message]],
                        'message' => $message,
                    ], 422);
                }

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', $message);
            }

            $message = 'Failed to update user due to a database error.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user: ' . $e->getMessage(),
                ], 500);
            }

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
    public function destroy(Request $request, User $user)
    {
        $isAjax = $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        $respond = function (bool $success, string $message, int $code) use ($isAjax) {
            if ($isAjax) {
                return response()->json(['success' => $success, 'message' => $message], $code);
            }

            return redirect()
                ->back()
                ->with($success ? 'success' : 'error', $message);
        };

        // Only engineers/administrators may delete accounts (matches
        // StoreUserRequest/UpdateUserRequest authorization for this module).
        $authUser = $request->user();
        if (!$authUser || $authUser->role !== 'engineer') {
            return $respond(false, 'You are not authorized to perform this action.', 403);
        }

        // Never allow a user to delete their own account mid-session.
        if ($authUser->user_id === $user->user_id) {
            return $respond(false, 'You cannot delete your own account while logged in.', 422);
        }

        // Never allow the last remaining engineer/administrator account to
        // be deleted, or the system becomes unmanageable.
        if ($user->role === 'engineer') {
            $remainingEngineers = User::where('role', 'engineer')
                ->where('user_id', '!=', $user->user_id)
                ->count();

            if ($remainingEngineers === 0) {
                return $respond(false, 'Cannot delete the last remaining Engineer/Administrator account.', 422);
            }
        }

        try {
            $hasRelatedRecords = (
                $user->engineeredProjects()->exists() ||
                $user->supervisedProjects()->exists() ||
                $user->attendanceLogs()->exists() ||
                $user->submittedReports()->exists()
            );

            // A client's own profile row cascade-deletes safely when the
            // user is removed (clients.user_id -> users.user_id is ON
            // DELETE CASCADE). What actually blocks deletion at the DB
            // level is a project still pointing at that client profile
            // (projects.client_id -> clients.client_id has no cascade),
            // so check that specifically instead of just "has a client
            // profile at all" — otherwise every client account becomes
            // permanently undeletable.
            if (!$hasRelatedRecords && $user->client) {
                $hasRelatedRecords = DB::table('projects')
                    ->where('client_id', $user->client->client_id)
                    ->exists();
            }

            if ($hasRelatedRecords) {
                return $respond(false, 'This user cannot be deleted because they are assigned to or related to existing records.', 422);
            }

            // Use the Eloquent model's own delete() rather than a raw query
            // builder delete, so model events/observers (if any exist or
            // are added later) still fire correctly.
            $user->delete();

            return $respond(true, 'User deleted successfully!', 200);

        } catch (QueryException $e) {
            // MySQL error code 1451 = FK constraint violation (some related
            // record we didn't explicitly check still references this
            // user). Surface it as a friendly message instead of a raw
            // SQL error.
            $message = (($e->errorInfo[1] ?? null) == 1451)
                ? 'This user cannot be deleted because related records still reference them.'
                : 'Failed to delete user due to a database error.';

            return $respond(false, $message, 500);

        } catch (\Exception $e) {
            return $respond(false, 'Failed to delete user: ' . $e->getMessage(), 500);
        }
    }
}