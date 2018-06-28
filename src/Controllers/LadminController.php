<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use App\Products;
use Eav\Attribute;
use Eav\Entity;
use Eav\EntityAttribute;
use Eav\Grid\Filter;
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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
                    $eavGrid = $grid->column($attr->attribute_code,$attr->frontend_label);
                    if ($attr->list_field_html) {
                        $eavGrid = $eavGrid->display(function($val){
//todo list_field_html
                        });
                    }
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
                if (!$attr->not_list && $attr->backend_type <> 'text' && $attr->is_filterable) {
                    $ft = $attr->frontend_type;
                    if ($ft == 'select' || $ft == 'radio'){
                        $filter->use((new Filter\Equal($attr->attribute_code.'_attr.value',$attr->frontend_label)))->{$ft}($attr->options());
                    } elseif($ft == 'multipleSelect'|| $ft == 'checkbox'){
                        $filter->use((new Filter\In($attr->attribute_code.'_attr.value',$attr->frontend_label))->{$ft}($attr->options()));
                    } elseif ($ft == 'datetime' || $ft == 'date' || $ft == 'time' || $ft == 'day' || $ft == 'month' || $ft == 'year'){
                        $filter->use((new Filter\Between($attr->attribute_code.'_attr.value',$attr->frontend_label))->{$ft}());
                    } elseif ($ft == 'currency' || $ft == 'decimal' || $ft == 'number' || $ft == 'rate'){
                        $filter->use(new Filter\Between($attr->attribute_code.'_attr.value',$attr->frontend_label));
                    } else {
                        $filter->use(new Filter\Like($attr->attribute_code.'_attr.value',$attr->frontend_label));
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
//        $rule = [''=>Rule::unique('product_varchar')->where(function ($query) {$query->where('attribute_id',3);})];
//        Validator::make(Input::all(), $rule);
        return Admin::form($this->entity->entity_class, function (Form $form) {
//            dd($this->attrsOnGroup()->groupBy('attribute_group_id')->toArray());
            $form->id('id','ID');
            foreach ($this->attrsOnGroup()->groupBy('attribute_group_id') as $attrGroup) {
                $form->tab($attrGroup->first()->attribute_group->attribute_group_name, function ($form) use ($attrGroup) {
                    foreach ($attrGroup as $entityAttr) {
                        $attr = $entityAttr->attribute;
                        $attField = $form->{$attr->frontend_type}($attr->attribute_code,$attr->frontend_label);
                        if ($attr->frontend_type == 'select' || $attr->frontend_type == 'multipleSelect' ||
                            $attr->frontend_type == 'checkbox' || $attr->frontend_type == 'radio')
                            $attField = $attField->options($attr->options());
                        if ($attr->is_required) $attField = $attField->attribute('required','required');
                        if ($attr->is_unique) $attField = $attField->rules(['required',
                            Rule::unique($attr->getBackendTable(),'value')->where(function ($query) use ($attr)
                                {$query->where('attribute_id',$attr->attribute_id);}
                            )]);
                        if ($attr->default_value) $attField = $attField->default($attr->default_value);
                        if ($attr->required_validate_class) $attField = $attField->addElementClass($attr->required_validate_class);
                        if ($attr->placeholder) $attField = $attField->placeholder($attr->placeholder);
                        if ($attr->help) $attField = $attField->help($attr->help);
                    }
                });
            }
        });
    }

    private function attrs()
    {
        return Attribute::where('entity_id',$this->entity->entity_id)->get();
    }

    private function attrsOnGroup()
    {
        $attribute_set_id = false ? : $this->entity->default_attribute_set_id;
        return EntityAttribute::where('entity_id',$this->entity->entity_id)
            ->where('attribute_set_id',$attribute_set_id)->with(['attribute','attribute_group'])->get();
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
