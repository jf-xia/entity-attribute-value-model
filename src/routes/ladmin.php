<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('eav.route.prefix'),
    'namespace'     => config('eav.route.namespace'),
    'middleware'    => config('eav.route.middleware'),
], function (Router $router) {

    $router->get('/', 'LadminController@index');
    $router->resource('/entity', 'EntityController');
    $router->resource('/attribute', 'AttributeController');

});
