<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use App\Products;
use Eav\Admin\Widgets\RelationGrid;
use Eav\Attribute;
use Eav\AttributeGroup;
use Eav\AttributeSet;
use Eav\Entity;
use Eav\EntityAttribute;
use Eav\EntityRelation;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
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
            $this->getColumn($grid,$this->getEAttributes());
            //todo get Columns/Tools/Actions/Export & CURD/RowSelector/Buttons with permission
            $this->getActions($grid);
            $this->getTools($grid);
            $this->getFilter($grid);
//            $grid->disableExport();
        });
    }

    public function getColumn($grid,$attrs)
    {
        foreach ($attrs as $attr) {
            if (!$attr->not_list && $attr->backend_type<>'text'){
                $eavGrid = $grid->column($attr->attribute_code,$attr->frontend_label);
                if ($attr->list_field_html) {
                    //<a target="_blank" href="https://item.jd.com/%value%.html" alt="SKU" >%value%</a>
                    $eavGrid = $eavGrid->display(function($val) use ($attr){
                        return $attr->getListHtml($val);
                    });
                }
                $eavGrid = $eavGrid->sortable();
            }
        }
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
            foreach ($this->getEAttributes() as $attr) {
                if ($attr->backend_type <> 'text' && $attr->is_filterable) {//!$attr->not_list &&
                    $ft = $attr->frontend_type;
                    if ($ft == 'select' || $ft == 'radio'){
                        $filter->equal($attr->attribute_code,$attr->frontend_label)->{$ft}($attr->options());
                    } elseif($ft == 'multipleSelect'|| $ft == 'checkbox'){
                        $filter->in($attr->attribute_code,$attr->frontend_label)->{$ft}($attr->options());
                    } elseif ($ft == 'datetime' || $ft == 'date' || $ft == 'time' || $ft == 'day' || $ft == 'month' || $ft == 'year'){
                        $filter->between($attr->attribute_code,$attr->frontend_label)->{$ft}();
                    } elseif ($ft == 'currency' || $ft == 'decimal' || $ft == 'number' || $ft == 'rate'){
                        $filter->between($attr->attribute_code,$attr->frontend_label);
                    } else {
                        $filter->like($attr->attribute_code,$attr->frontend_label);
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
        $content->body($this->form()->edit($id).$this->formLists());
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
     * Make a relation entity list below form.
     *
     * @return Form
     */
    protected function formLists()
    {
        $tab = new Tab();
        //todo edit map to relation entity by entity_relation_ids
        foreach ($this->entity->entity_relations->groupBy('relation_entity_id') as $entity_relation) {
            $entity = $entity_relation->first()->relation;
            $entityObject = $entity->entity_class;
            $grid = new RelationGrid(new $entityObject(),function(RelationGrid $grid) use ($entity_relation,$entity){//
                $grid->model()->whereIn('id',$entity_relation->pluck('entity_relation_object_id'));
//                $grid->id('ID')->sortable();
                $grid->column('',trans('eav::eav.action'))->display(function() use ($entity){
                    return '<a href="'.admin_url($entity->entity_code.'/'.$this->getKey())
                        .'/edit" target="_blank" ><i class="fa fa-edit"></i></a>';
                    //todo delete button
                });
                $this->getColumn($grid,$entity_relation->first()->relation->attributes);
                $grid->setBoxFooter('. -- 点击链接查看表单：<a href="'.admin_url($entity->entity_code)
                    .'" target="_blank" >'.$entity->entity_name.'</a>');
            });
//            $tab->dropDown([$entity->entity_name,admin_url($entity->entity_code)]);
            $tab->add($entity->entity_name,$grid);
        }
        $formLists = new Box(trans('eav::eav.entity_relations'),$tab);
        return $formLists;
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
//            $form->text('relation_entity.id','relat');
            //todo get attribute_set_id
            foreach ($this->entity->defaultAttributeSet->attribute_group as $attrGroup) {
                $form->tab($attrGroup->attribute_group_name, function ($form) use ($attrGroup) {
                    foreach ($attrGroup->attributes as $attr) {
                        if ($attr->frontend_type == 'hasone') {
                            if (!$enitiy = Entity::where('entity_code', '=', $attr->attribute_code)->first()) continue;
                            $entityClass = $enitiy->entity_class;
                            $attField = $form->select($attr->attribute_code,$attr->frontend_label)->options(
                                $entityClass::all()->pluck('name','id')
                            );
                        } else {
                            $attField = $form->{$attr->frontend_type}($attr->attribute_code,$attr->frontend_label);
                        }
                        if ($attr->frontend_type == 'select' || $attr->frontend_type == 'multipleSelect' ||
                            $attr->frontend_type == 'checkbox' || $attr->frontend_type == 'radio')
                            $attField = $attField->options($attr->options());
                        if ($attr->is_required) $attField = $attField->attribute('required','required');
                        if ($attr->is_unique) $attField = $attField->rules(['required',
                            Rule::unique($attr->getBackendTable(),'value')->where(function ($query) use ($attr)
                                {$query->where('id',$attr->id);}
                            )]);
                        if ($attr->default_value) $attField = $attField->default($attr->default_value);
                        if ($attr->required_validate_class) $attField = $attField->addElementClass($attr->required_validate_class);
                        if ($attr->placeholder) $attField = $attField->placeholder($attr->placeholder);
                        if ($attr->help) $attField = $attField->help($attr->help);
                        //todo form_field_html
                    }
                });
            }
        });
    }

    private function getEAttributes()
    {
//        return Attribute::where('entity_id',$this->entity->entity_id)->get();
        return $this->entity->attributes;
    }

    private function attrsOnGroup()
    {//todo
//        $attribute_set_id = false ? : $this->entity->default_attribute_set_id;
//        ->firstWhere('attribute_set_id',$attribute_set_id);//->with('attributes')->get()
        $attrsOnGroup = $this->entity->defaultAttributeSet->attribute_group;
        dd($attrsOnGroup);
        return $attrsOnGroup;
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
