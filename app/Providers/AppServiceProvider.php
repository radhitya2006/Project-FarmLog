<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $limited = fn (Request $request, array $headers) => response()->view('errors.429', [
            'retryAfter' => $headers['Retry-After'] ?? 60,
        ], 429, $headers);

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by($request->ip())->response($limited));
        RateLimiter::for('register', fn (Request $request) => Limit::perHour(3)
            ->by($request->ip())->response($limited));
    }
}
