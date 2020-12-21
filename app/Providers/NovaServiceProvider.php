<?php

declare(strict_types=1);

namespace App\Providers;

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

use App\Nova\Cards\MakeAWish;
use App\Nova\Metrics\VisitsBySpace;
use App\Nova\Metrics\VisitsPerDay;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Vyuldashev\NovaPermission\NovaPermissionTool;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
        Nova::serving(static function (ServingNova $event): void {
            Nova::script('people-counter-custom', asset('js/nova.js'));
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes(): void
    {
        Nova::routes()->withAuthenticationRoutes()->withPasswordResetRoutes()->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate(): void
    {
        Gate::define('viewNova', static function (User $user): bool {
            return $user->hasRole('super-admin') || $user->can('access-nova');
        });
    }

    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array<\Laravel\Nova\Card>
     */
    protected function cards(): array
    {
        return [
            new VisitsPerDay(),
            new VisitsBySpace(),
            new MakeAWish(),
        ];
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array<\Laravel\Nova\Dashboard>
     */
    protected function dashboards(): array
    {
        return [];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array<\Laravel\Nova\Tool>
     */
    public function tools(): array
    {
        return [
            (new NovaPermissionTool())->canSee(static function (Request $request): bool {
                return $request->user()->hasRole('super-admin');
            }),
        ];
    }
}
