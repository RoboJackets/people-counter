<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Set up Horizon authentication.
         *
         * @phan-suppress PhanPossiblyUndeclaredMethod
         * @phan-suppress PhanPluginAlwaysReturnFunction
         */
        Horizon::auth(static function (): bool {
            if (
                auth()->guard('web')->user() instanceof User
                && auth()->guard('web')->user()->can('access-horizon')
            ) {
                return true;
            }

            if (null === auth()->guard('web')->user()) {
                // Theoretically, this should never happen since we're calling the CAS middleware before this.
                abort(401, 'Authentication Required');
            }

            abort(403, 'Forbidden');
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();
    }
}
