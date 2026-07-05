<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        Model::shouldBeStrict(! $this->app->isProduction());
        DB::prohibitDestructiveCommands($this->app->isProduction());

        $this->configureRateLimiters();
    }

    /**
     * Auth uç noktaları için isimli hız sınırlayıcıları tanımlar.
     */
    private function configureRateLimiters(): void
    {
        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)
            ->by(($request->ip() ?? '').'|'.$request->string('email')->value()));

        RateLimiter::for('register', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($request->ip() ?? ''));

        RateLimiter::for('forgot-password', fn (Request $request): Limit => Limit::perMinute(3)
            ->by(($request->ip() ?? '').'|'.$request->string('email')->value()));

        RateLimiter::for('reset-password', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($request->ip() ?? ''));
    }
}
