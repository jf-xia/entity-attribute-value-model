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
        $content->body($this->grid());
        return $content;
    }

    public function grid()
    {
        return Admin::grid($this->entity->entity_class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            foreach ($this->attrs() as $attr) {
                if (!$attr->not_list && $attr->backend_type<>'text'){
                    $grid->column($attr->attribute_code,$attr->frontend_label);
                }
            }
            $this->getActions($grid);
            $this->getTools($grid);
            $this->getFilter($grid);
//            $grid->disableExport();
        });
    }

    public function getActions($grid)
    {
//        $grid->disableCreateButton();
        if(!Admin::user()->isAdministrator()){
            $grid->actions(function ($actions) {
                $actions->disableDelete();
            });
        }
    }

    public function getTools($grid)
    {
        if(!Admin::user()->isAdministrator()){
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
        }
    }

    public function getFilter($grid)
    {
        $grid->filter(function ($filter)  {
            $filter->disableIdFilter();
            foreach ($this->attrs() as $attr) {
                if (!$attr->not_list && $attr->backend_type <> 'text') {
                    $ft = $attr->frontend_type;
                    if ($ft == 'select' || $ft == 'radio'){
                        $filter->equal($attr->attribute_code,$attr->frontend_label)->select($attr->options());
                    } elseif($ft == 'multipleSelect'|| $ft == 'checkbox'){
                        $filter->in($attr->attribute_code,$attr->frontend_label)->multipleSelect($attr->options());
                    } elseif ($ft == 'datetime' || $ft == 'date'){
                        $filter->between($attr->attribute_code,$attr->frontend_label)->datetime();
                    } elseif ($ft == 'currency' || $ft == 'decimal' || $ft == 'number' || $ft == 'rate'){
                        $filter->between($attr->attribute_code,$attr->frontend_label);
                    } else {
                        $filter->like($attr->attribute_code.'_attr.value',$attr->frontend_label);
                    }
                }
            }
        });
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
                $attField = $form->{$attr->frontend_type}($attr->attribute_code,$attr->frontend_label);
                if ($attr->frontend_type == 'select' || $attr->frontend_type == 'multipleSelect' ||
                    $attr->frontend_type == 'checkbox' || $attr->frontend_type == 'radio')
                    $attField = $attField->options($attr->options());
                if($attr->is_required) {
                    $attField = $attField->attribute('required','required');
                }
                if($attr->default_value) {
                    $attField = $attField->default($attr->default_value);
                }
                if($attr->required_validate_class) {
                    $attField = $attField->addElementClass($attr->required_validate_class);
                }
            }
        });
    }

    private function attrs()
    {
        return Attribute::where('entity_id',$this->entity->entity_id)->orderBy('order')->get();
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
