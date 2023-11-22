<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            $this->adminRoutes();
            $this->adminAuthRoutes();
        });
    }
    /**
     * Define the "admin" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */

    protected function adminRoutes()
    {
        Route::name('admin.')->prefix(config('app.admin-route-prefix'))
            ->middleware(['web','auth','twoFactor','isLoggedIn','isAdmin'])
            ->namespace($this->namespace)
            ->group(base_path('routes/admin.php'));
    }
    protected function adminAuthRoutes()
    {
        Route::name('auth.')->prefix(config('app.auth-route-prefix'))
            ->middleware(['web'])
            ->namespace($this->namespace)
            ->group(base_path('routes/auth.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
