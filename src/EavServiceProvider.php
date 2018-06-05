<?php

namespace Vreap\Eav;

use Illuminate\Support\ServiceProvider;

class EavServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
//        'Vreap\Eav\Console\MakeCommand',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
//        'admin.auth'       => \Vreap\Eav\Middleware\Authenticate::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
//        'admin' => [
//            'admin.auth',
//        ],
    ];

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
//        $this->loadViewsFrom(__DIR__.'/../resources/views', 'admin');
//
//        if (file_exists($routes = admin_path('routes.php'))) {
//            $this->loadRoutesFrom($routes);
//        }
//
//        if ($this->app->runningInConsole()) {
//            $this->publishes([__DIR__.'/../config' => config_path()], 'laravel-admin-config');
//            $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'laravel-admin-lang');
////            $this->publishes([__DIR__.'/../resources/views' => resource_path('views/admin')],           'laravel-admin-views');
//            $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'laravel-admin-migrations');
//            $this->publishes([__DIR__.'/../resources/assets' => public_path('vendor/laravel-admin')], 'laravel-admin-assets');
//        }
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
