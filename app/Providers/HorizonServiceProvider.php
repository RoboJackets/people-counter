<?php

declare(strict_types=1);

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Horizon::night();
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function gate()
    {
        // phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
        // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

        Gate::define('viewHorizon', static function (User $user): bool {
            return true;
        });
    }
}
