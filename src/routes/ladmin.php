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
    $router->get('/entity/ajax/attrs', 'EntityController@getDisplayAttrsAjax');
    $router->get('/entity/ajax/options', 'EntityController@getOptionsAjax');
    $router->resource('/attribute', 'AttributeController');
    $router->resource('/attributeset', 'AttributeSetController');
    $router->any('/attr/set', 'AttributeSetController@attrSetStore');
    $router->get('/attr/set/{id}', 'AttributeSetController@attrSetDelete');
    $router->any('/attr/group', 'AttributeSetController@attrGroupStore');
    $router->get('/attr/group/{id}', 'AttributeSetController@attrGroupDelete');
    $router->post('/attr/setmap', 'AttributeSetController@attrMap');

});

//Form::forget(['map', 'editor']);
Form::extend('subForm', Extensions\FormHasMany::class);

