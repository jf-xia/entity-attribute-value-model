<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use Eav\Attribute;
use Eav\Entity;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

class AttributeController extends Controller
{
    public function index()
    {
        $content = Admin::content();
        $content->header('Dashboard');
        $content->description('Description...');

//        $content->row(Dashboard::title());
//        $product = Products::whereAttribute('name','ddd')->get();
        //$product = Products::all(['attr.*'])->where('name','ddd');
        $grid = '';
        $entities = Entity::all();
        foreach ($entities as $entity) {
            $grid .= Admin::grid($entity->entity_class, function (Grid $grid) use ($entity) {
                $grid->id('ID')->sortable();
                $attrs = Attribute::where('entity_id',$entity->entity_id)->get();
                foreach ($attrs as $attr) {
                    $grid->column($attr->attribute_code,$attr->frontend_label);
                }
            });
        }
        $content->body($grid);
        return $content;
//        $product->name= 'dsafdasfafd';
//        $product->save();
//        \DB::enableQueryLog();
//        dd($product,\DB::getQueryLog());
//        dd(Products::class);
//        dd($content);
//        $content->row(function (Row $row) {});
    }
}
