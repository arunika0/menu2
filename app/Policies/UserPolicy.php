<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function isSuperAdmin(User $user)
    {
        return $user->role === 'super_admin';
    }

    public function isRestaurantAdmin(User $user)
    {
        return $user->role === 'restaurant_admin';
    }
}
