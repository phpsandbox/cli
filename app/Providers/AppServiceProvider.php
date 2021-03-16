<?php

namespace App\Providers;

use App\Contracts\AuthenticationContract;
use App\Contracts\BrowserContract;
use App\Contracts\ZipExportContract;
use App\Services\Authentication;
use App\Services\BrowserService;
use App\Services\ZipExportService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public array $bindings = [
        BrowserContract::class => BrowserService::class,
        AuthenticationContract::class => Authentication::class,
        ZipExportContract::class => ZipExportService::class,
    ];
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
