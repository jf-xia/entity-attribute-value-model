<?php

use Illuminate\Routing\Router;
use Encore\Admin\Form;
use Eav\Admin\Extensions;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('eav.route.prefix'),
    'namespace'     => config('eav.route.namespace'),
    'middleware'    => config('eav.route.middleware'),
], function (Router $router) {
//'index', 'create', 'store', 'show', 'edit', 'update', 'destroy'
    $router->get('/b/{entity}', 'LadminController@index');
    $router->post('/b/{entity}', 'LadminController@store');
    $router->get('/b/{entity}/create', 'LadminController@create');
    $router->get('/b/{entity}/{id}', 'LadminController@show');
    $router->get('/b/{entity}/{id}/edit', 'LadminController@edit');
    $router->post('/b/{entity}/{id}', 'LadminController@update');
    $router->post('/b/{entity}/{id}', 'LadminController@destroy');
    $router->resource('/entity', 'EntityController');
    $router->resource('/attribute', 'AttributeController');

});

//Form::forget(['map', 'editor']);
Form::extend('subForm', Extensions\FormHasMany::class);

