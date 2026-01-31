<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerUploadsHelpers();
    }

    /**
     * Load helper uploads_asset & uploads_base_path (untuk public_html / uploads).
     */
    private function registerUploadsHelpers(): void
    {
        $helperPath = app_path('Helpers/uploads_asset.php');
        if (file_exists($helperPath)) {
            require_once $helperPath;
            return;
        }
        if (!function_exists('uploads_base_path')) {
            function uploads_base_path(string $path = ''): string
            {
                $base = is_dir(base_path('public_html'))
                    ? base_path('public_html')
                    : public_path();
                return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path, '/\\'));
            }
        }
        if (!function_exists('uploads_asset')) {
            function uploads_asset(?string $path): string
            {
                if (!$path) {
                    return asset('');
                }
                $p = ltrim(str_replace('\\', '/', $path), '/');
                if (str_starts_with($p, 'storage/')) {
                    return asset($p);
                }
                return asset('uploads/' . $p);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
