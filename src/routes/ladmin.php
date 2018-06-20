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
    foreach (\Eav\Entity::all() as $entity) {
        $router->resource($entity->entity_code, 'LadminController');
    }
    $router->resource('/entity', 'EntityController');
    $router->resource('/attribute', 'AttributeController');

});

//Form::forget(['map', 'editor']);
Form::extend('subForm', Extensions\FormHasMany::class);

