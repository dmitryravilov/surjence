<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\HeadlineService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HeadlineService::class, function () {
            $url = config('services.go_service.url');
            assert(is_string($url));

            return new HeadlineService($url);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
