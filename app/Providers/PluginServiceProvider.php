<?php

namespace App\Providers;

use function foo\func;
use Illuminate\Support\ServiceProvider;
use App\Services\PluginManager;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton(PluginManager::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
