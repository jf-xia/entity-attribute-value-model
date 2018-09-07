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
//    $router->get('/', function () { return redirect('admin/auth/users'); });

    foreach (\Eav\Entity::all() as $entity) {
        if (\Illuminate\Support\Facades\Schema::hasTable('entities')) {
            $router->resource($entity->entity_code, 'LadminController');
        }
    }
    $router->resource('/entity', 'EntityController');
    $router->get('/entity/ajax/attrs', 'EntityController@getDisplayAttrsAjax');
    $router->get('/entity/ajax/options', 'EntityController@getOptionsAjax');
    $router->any('/entity/{entity}/attr/set', 'EntityController@attrSetStore');
    $router->get('/entity/{entity}/attr/set/{id}', 'EntityController@attrSetDelete');
    $router->any('/entity/{entity}/attr', 'EntityController@attrStore');
    $router->get('/entity/{entity}/attr/{id}', 'EntityController@attrDelete');
    $router->get('/entity/{entity}/attr/{attrid}/permission/{attrcode}/name/{label}', 'EntityController@attrPermission');
    $router->post('/entity/{entity}/attr/setmap', 'EntityController@attrMap');
    $router->resource('/attribute', 'AttributeController');
    $router->resource('/attributeset', 'AttributeSetController');
    $router->resource('bpmn/workflow', 'WorkflowController');
    $router->get('bpmn/workflow/ajax/get/{id}', 'WorkflowController@ajaxBpmnViewer');
    $router->post('bpmn/workflow/ajax/update/{id}', 'WorkflowController@ajaxBpmnSave');
    $router->resource('bpmn/process', 'ProcessController');
    $router->resource('bpmn/processitem', 'ProcessItemController');
//    $router->any('/attr/set', 'AttributeSetController@attrSetStore');
//    $router->get('/attr/set/{id}', 'AttributeSetController@attrSetDelete');
//    $router->post('/attr/setmap', 'AttributeSetController@attrMap');
//    $router->any('/attr/group', 'AttributeSetController@attrGroupStore');
//    $router->get('/attr/group/{id}', 'AttributeSetController@attrGroupDelete');

});

//Form::forget(['map', 'editor']);
Form::extend('subForm', Extensions\FormHasMany::class);
Form::extend('multipleSelectString', Extensions\MultipleSelectString::class);
//Form::extend('bpmn', Extensions\Bpmn::class);

