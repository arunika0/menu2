<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        User::class => \App\Policies\UserPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::define('isSuperAdmin', function ($user) {
            return $user->role === 'super_admin';
        });

        Gate::define('isRestaurantAdmin', function ($user) {
            return $user->role === 'restaurant_admin';
        });
    }
}
