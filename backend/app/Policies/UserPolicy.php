<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserPolicy
{
    /**
     * Determine whether the authenticated user may view the target user.
     */
    public function view(User $user, User $target): bool
    {
        return $user->is($target)
            || Gate::forUser($user)->allows('manage-users');
    }

    /**
     * Determine whether the authenticated user may update the target user.
     */
    public function update(User $user, User $target): bool
    {
        return $user->is($target)
            || Gate::forUser($user)->allows('manage-users');
    }
}
