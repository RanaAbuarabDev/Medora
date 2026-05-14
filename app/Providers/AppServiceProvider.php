<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\LabScheduleRepositoryInterface;
use App\Repositories\LabScheduleRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // سطر الربط السحري 🪄
        $this->app->bind(LabScheduleRepositoryInterface::class, LabScheduleRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تم نقل كود تحديد معدل الطلبات (Rate Limiter) لهنا ليعمل بدون مشاكل
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                optional($request->user())->id ?: $request->ip()
            );
        });
    }
}