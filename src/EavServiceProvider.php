<?php

namespace Eav;

use Illuminate\Support\ServiceProvider;

class EavServiceProvider extends ServiceProvider
{

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'eav');
        $this->mergeConfigFrom(__DIR__.'/../config/eav.php', 'eav');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'eav');

        if (file_exists($routes = __DIR__.'/routes/ladmin.php')) {
            $this->loadRoutesFrom($routes);
        }

        if ($this->app->runningInConsole()) {

        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }

}
