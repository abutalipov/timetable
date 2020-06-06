<?php

namespace App\Providers;

use App\Services\Auth\JWTGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
         'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::extend('jwt', function($app, $name, array $config) {
            $request = app('request');
            return new JWTGuard(Auth::createUserProvider($config['provider']), $request);
        });
        $this->registerPolicies();
    }
}
