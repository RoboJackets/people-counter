<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Horizon::auth(static function (): bool {
            // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
            if (auth()->guard('web')->user() instanceof User
                // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
                && auth()->guard('web')->user()->can('access-horizon')
            ) {
                return true;
            }

            // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
            if (null === auth()->guard('web')->user()) {
                // Theoretically, this should never happen since we're calling the CAS middleware before this.
                abort(401, 'Authentication Required');
            }

            abort(403, 'Forbidden');

            return false;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
    }
}
