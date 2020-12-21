<?php

declare(strict_types=1);

namespace App\Providers;

use App\Policies\SpacePolicy;
use App\Policies\UserPolicy;
use App\Policies\VisitPolicy;
use App\Models\Space;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<string,string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Visit::class => VisitPolicy::class,
        Space::class => SpacePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
