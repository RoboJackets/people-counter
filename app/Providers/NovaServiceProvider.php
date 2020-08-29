<?php

declare(strict_types=1);

namespace App\Providers;

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

use App\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
        Nova::serving(static function (ServingNova $event): void {
            Nova::script('people-counter-custom', __DIR__.'/../../public/js/nova.js');
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()->withAuthenticationRoutes()->withPasswordResetRoutes()->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function gate()
    {
        // phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
        // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

        Gate::define('viewNova', static function (User $user): bool {
            return $user->hasRole('super-admin');
        });
    }

    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array<\Laravel\Nova\Card>
     */
    protected function cards()
    {
        return [
            new Help(),
        ];
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array<\Laravel\Nova\Dashboard>
     */
    protected function dashboards()
    {
        return [];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array<\Laravel\Nova\Tool>
     */
    public function tools()
    {
        return [
            \Vyuldashev\NovaPermission\NovaPermissionTool::make(),
        ];
    }
}
