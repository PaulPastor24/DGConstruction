<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class AdminAccountGuard
{
    public const ROLES = ['engineer', 'admin', 'administrator'];

    /** Call within the transaction that changes or deletes the account. */
    public function ensureAdminRemains(User $user, string $nextRole = '', bool $nextActive = false): void
    {
        // Lock the same rows in a stable order for concurrent account changes.
        $admins = User::query()->whereIn('role', self::ROLES)
            ->orderBy('user_id')->lockForUpdate()->get();
        $current = $admins->firstWhere('user_id', $user->user_id);

        if (! $current || ! $current->is_active
            || ($nextActive && in_array($nextRole, self::ROLES, true))) {
            return;
        }

        if (! $admins->contains(fn ($admin) => $admin->user_id !== $user->user_id && $admin->is_active)) {
            throw ValidationException::withMessages([
                'user' => 'At least one active Engineer/Administrator account must remain. Create or activate another administrator first.',
            ]);
        }
    }
}
