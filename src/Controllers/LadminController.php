<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use Eav\Attribute;
use Eav\Entity;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class LadminController extends Controller
{
    use ModelForm;
    private $entityCode;
    private $entity;

    public function __construct()
    {
        $this->entityCode = explode('.',Route::currentRouteName())[0];
        $this->entity = Entity::findByCode($this->entityCode);
        if(empty($this->entity)){
            abort(404);
        }
    }

    public function index()
    {
        $content = Admin::content();
        $content->header($this->entity->entity_name.trans('eav::eav.list'));
        $content->description($this->entity->entity_desc);

        $grid = Admin::grid($this->entity->entity_class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            foreach ($this->attrs() as $attr) {
                $grid->column($attr->attribute_code,$attr->frontend_label);
            }
        });
        $content->body($grid);
        return $content;
    }

    private function attrs()
    {
        return Attribute::where('entity_id',$this->entity->entity_id)->get();
    }

    public function edit($id)
    {
        $content = Admin::content();
        $content->header($this->entity->entity_name.trans('eav::eav.edit'));
        $content->description($this->entity->entity_desc);
        $content->body($this->form()->edit($id));
        return $content;
    }

    public function create()
    {
        $content = Admin::content();
        $content->header($this->entity->entity_name.trans('eav::eav.create'));
        $content->description($this->entity->entity_desc);
        $content->body($this->form());
        return $content;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form($this->entity->entity_class, function (Form $form) {
            $form->id('id','ID');
            foreach ($this->attrs() as $attr) {
                $form->{$attr->frontend_type}($attr->attribute_code,$attr->frontend_label);
            }
        });
    }

//    public function test()
//    {
////        $content->row(Dashboard::title());
////        $product = Products::whereAttribute('name','ddd')->get();
//        //$product = Products::all(['attr.*'])->where('name','ddd');
//        $grid = '';
//        $entities = Entity::all();
//        foreach ($entities as $entity) {
//            $grid .= Admin::grid($entity->entity_class, function (Grid $grid) use ($entity) {
//                $grid->id('ID')->sortable();
//                $attrs = Attribute::where('entity_id',$entity->entity_id)->get();
//                foreach ($attrs as $attr) {
//                    $grid->column($attr->attribute_code,$attr->frontend_label);
//                }
//            });
//        }
//        $content->body($grid);
//        return $content;
////        $product->name= 'dsafdasfafd';
////        $product->save();
////        \DB::enableQueryLog();
////        dd($product,\DB::getQueryLog());
////        dd(Products::class);
////        dd($content);
////        $content->row(function (Row $row) {});
//
//    }
}
