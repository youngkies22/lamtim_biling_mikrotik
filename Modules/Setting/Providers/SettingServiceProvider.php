<?php

namespace Modules\Setting\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'setting');
        
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../Routes/web.php');
    }
}
