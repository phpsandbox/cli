<?php

namespace App\Providers;

use App\Contracts\AuthenticationContract;
use App\Contracts\BrowserContract;
use App\Contracts\ZipExportContract;
use App\Services\AuthenticationService;
use App\Services\BrowserService;
use App\Services\ZipExportService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        BrowserContract::class        => BrowserService::class,
        AuthenticationContract::class => AuthenticationService::class,
        ZipExportContract::class      => ZipExportService::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
    }

    public function boot(): void
    {
        File::macro('fileSizeInMB', function (string $path) {
            return (float) $this->size($path) / 1048576;
        });

        File::macro('countFiles', function (string $path, array $ignore = []) {
            $number_of_files = 0;
            foreach (scandir($path) as $file) {
                if (in_array($file, array_merge($ignore, ['.', '..']))) {
                    continue;
                }

                if (is_dir(rtrim($path, '/') . '/' . $file)) {
                    $number_of_files += $this->countFiles(rtrim($path, '/') . '/' . $file);
                } else {
                    $number_of_files++;
                }
            }

            return $number_of_files;
        });
    }
}
