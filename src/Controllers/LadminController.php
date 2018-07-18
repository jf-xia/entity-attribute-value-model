<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use App\Oa;
use App\Products;
use Eav\Admin\Widgets\RelationGrid;
use Eav\Attribute;
use Eav\AttributeGroup;
use Eav\AttributeSet;
use Eav\Entity;
use Eav\EntityAttribute;
use Eav\EntityRelation;
use Encore\Admin\Auth\Database\Permission;
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

    private $eavModel;

    private $attrPermissions;

    public function __construct()
    {
        $this->entityCode = explode('.',Route::currentRouteName())[0];
        $this->entity = Entity::findByCode($this->entityCode);
        $this->attrPermissions = Permission::where('slug','like',$this->entity->entity_code.'_%')->get(['slug']);
        if(empty($this->entity)){
            abort(404);
        }
//        dd($this->entity->attributeSets);
    }

    public function index()
    {
        $content = Admin::content();
        $content->header($this->entity->entity_name.trans('eav::eav.list'));
        $content->description($this->entity->entity_desc);
        $content->body($this->grid());
        return $content;
    }

    /** todo 3 permission for ext actions & tools
     * get Columns/Tools/Actions/Export & CURD/RowSelector/Buttons with permission slug_format[entityCode_{action}_attrCode_attrId]
     *
     * @return Grid
     */
    public function grid()
    {
        return Admin::grid($this->entity->entity_class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $this->getColumn($grid);
            $this->getActions($grid);
            $this->getTools($grid);
            $this->getFilter($grid);
            if(!Admin::user()->can('export_'.$this->entity->entity_code)) $grid->disableExport();
        });
    }

    public function getColumn($grid,$attrs = null)
    {
        $attrs = $attrs ? : $this->entity->attributeSet->attributes;
        foreach ($attrs as $attr) {
            if (!$attr->not_list && $attr->backend_type<>'text'){
                if (!$this->canViewAttr($attr)) continue;
                // hasone format: {hasone/hasmany}2{entityCode}2{relationEntityAttrCode}2{anyCode}
                if ($attr->frontend_type == 'hasone' || $attr->frontend_type == 'hasmany') {
                    $attrCode = $attr->frontend_type.'2'.$attr->attribute_code.'.'.explode('2',$attr->attribute_code.'2name')[1];
                } else {
                    $attrCode = $attr->attribute_code;
                }
                $eavGrid = $grid->column($attrCode,$attr->frontend_label);
                if ($attr->list_field_html) {
                    //<a target="_blank" href="https://xxx.com/%value%.html" alt="SKU" >%value%</a>
                    $eavGrid = $eavGrid->display(function($val) use ($attr){
                        return $attr->getListHtml($val);
                    });
                }
                $eavGrid = $eavGrid->sortable();
            }
        }
    }

    public function canViewAttr($attr)
    {
        $slug = $this->entity->entity_code.'_view_'.$attr->attribute_code.'_'.$attr->id;
        if (!empty($this->attrPermissions->firstWhere('slug',$slug))) {
            if (!Admin::user()->can($slug)) return false;
        }
        return true;
    }

    public function getActions($grid)
    {
        $entityCode = $this->entity->entity_code;
        if(!Admin::user()->can('create_'.$entityCode)) $grid->disableCreateButton();
        $grid->actions(function ($actions) use ($entityCode) {
            if(!Admin::user()->can('delete_'.$entityCode)) $actions->disableDelete();
            if(!Admin::user()->can('update_'.$entityCode)) $actions->disableEdit();
        });
    }

    public function getTools($grid)
    {
        $entityCode = $this->entity->entity_code;
        $grid->tools(function ($tools) use ($entityCode) {
            $tools->batch(function ($batch) use ($entityCode) {
                if(!Admin::user()->can('delete_'.$entityCode)) $batch->disableDelete();
            });
        });
    }

    public function getFilter($grid)
    {
        $grid->filter(function ($filter)  {
            $filter->disableIdFilter();
            foreach ($this->entity->attributes as $attr) {
                if ($attr->backend_type <> 'text' && $attr->is_filterable) {//!$attr->not_list &&
                    if (!$this->canViewAttr($attr)) continue;
                    $ft = $attr->frontend_type;
                    if ($ft == 'select' || $ft == 'radio'){
                        $filter->equal($attr->attribute_code,$attr->frontend_label)->{$ft}($attr->options());
                    } elseif($ft == 'hasone'){
                        list($enitiy,$attrOptionsName) = $this->getHasone($attr);
                        if (!$enitiy) continue;
                        $filter->equal($attr->attribute_code,$attr->frontend_label)->select()
                            ->ajax('/admin/entity/ajax/options?entity='.base64_encode($enitiy->entity_class).'&option='.$attrOptionsName);
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

    public function getHasone($attr)
    {
        list($entityCode,$attrOptionsName) = explode('2',$attr->attribute_code.'2');
        $attrOptionsName = $attrOptionsName ? : 'name';
        $enitiy = Entity::where('entity_code', '=', $entityCode)->first();
        return [$enitiy,$attrOptionsName];
    }

    public function show($id)
    {
        return redirect(route($this->entityCode.'.edit',$id));
    }

    public function edit($id)
    {
        $modelClass = $this->entity->entity_class;
        $this->eavModel = $modelClass::find($id);
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
        // handle set attribute_set_id
        if ($attrSetId = Input::get('set')) {
            $attrSets = $this->getAttrSet();
            $attrSetOptionHtml = '';
            foreach ($attrSets as $attrSet) {
                $selected = $attrSetId== $attrSet->id? ' selected = "selected"' : '';
                $attrSetOptionHtml .= '<option value="'.$attrSet->id.'" '.$selected.' >'.$attrSet->attribute_set_name.'</option>';
            }
            $attrSetHtml = '<select onchange="window.location.href=\'?set=\'+$(this).children(\'option:selected\').val();"
                        class="form-control set">'.$attrSetOptionHtml.'</select>';
            $content->row($attrSetHtml);
        }
        $content->body($this->form());
        return $content;
    }

    public function getAttrSet()
    {
        return AttributeSet::where('entity_id',$this->entity->id)->get();
    }

    /**
     * Make a relation entity list below form.
     *
     * @return Form
     */
    protected function formLists()
    {
        $tab = new Tab();
        //todo 3 edit map to relation entity by entity_relation_ids 关联管理模块 m2m表管理
        foreach ($this->entity->entity_relations->groupBy('relation_entity_id') as $entity_relation) {
            $entity = $entity_relation->first()->relation;
            $entityObject = $entity->entity_class;
            $grid = new RelationGrid(new $entityObject(),function(RelationGrid $grid) use ($entity_relation,$entity){//
                $grid->model()->whereIn('id',$entity_relation->pluck('entity_relation_object_id'));
//                $grid->id('ID')->sortable();
                $grid->column('',trans('eav::eav.action'))->display(function() use ($entity){
                    return '<a href="'.admin_url($entity->entity_code.'/'.$this->getKey())
                    .'/edit" target="_blank" ><i class="fa fa-edit"></i></a>';
                    //todo 4 delete button
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
//            $form->multipleSelect('hasmany2oa2title','ddddd')->options(Oa::all()->pluck('title','id')); todo hasmany
            $form->id('id','ID');
            if (Admin::user()->can('update_'.$this->entity->entity_code) && explode('.',Route::currentRouteName())[1] == 'edit') {
                $form->select('attribute_set_id',trans('eav::eav.attribute_set_id'))->options($this->getAttrSet()->pluck('attribute_set_name','id'));
            } elseif (Input::get('set')) {
                $form->hidden('attribute_set_id')->value(Input::get('set'));
            }

            foreach ($this->entity->attributeSet->attribute_group as $attrGroup) {
                $form->tab($attrGroup->attribute_group_name, function ($form) use ($attrGroup) {
                    foreach ($attrGroup->attributes as $attr) {
                        $slug = $this->entity->entity_code.'_edit_'.$attr->attribute_code.'_'.$attr->id;
                        if (!empty($this->attrPermissions->firstWhere('slug',$slug))) {
                            if (!Admin::user()->can($slug)) continue;
                        }
                        if ($attr->frontend_type == 'hasone') {
                            list($enitiy,$attrOptionsName) = $this->getHasone($attr);
                            if (!$enitiy) continue;
                            $attField = $form->select($attr->attribute_code,$attr->frontend_label)->options(
                                function ($id) use ($enitiy,$attrOptionsName) {
                                    $entityClass = $enitiy->entity_class;
                                    $model = $entityClass::find($id);
                                    return $model ? [$model->id => $model->$attrOptionsName] : [];
                                })->ajax('/admin/entity/ajax/options?entity='.base64_encode($enitiy->entity_class).'&option='.$attrOptionsName);
                        } else {
                            $attField = $form->{$attr->frontend_type}($attr->attribute_code,$attr->frontend_label);
                        }
                        if ($attr->frontend_type == 'select' || $attr->frontend_type == 'multipleSelect' ||
                            $attr->frontend_type == 'checkbox' || $attr->frontend_type == 'radio')
                            $attField = $attField->options($attr->options());
                        if ($attr->is_required) $attField = $attField->attribute('required','required');
                        if ($attr->is_unique) $attField = $attField->rules(['required',
                            Rule::unique($attr->getBackendTable(),'value')
                                ->where(function ($query) use ($attr,$form)
                                {$query->where('attribute_id',$attr->id)->where('entity_id','<>',$form->model()->id);}
                                )]);
                        if ($attr->default_value) $attField = $attField->default($attr->default_value);
                        if ($attr->required_validate_class) $attField = $attField->addElementClass($attr->required_validate_class);
                        if ($attr->placeholder) $attField = $attField->placeholder($attr->placeholder);
                        if ($attr->help) $attField = $attField->help($attr->help);
                        //todo 4 form_field_html
                    }
                });
            }
            if(!Admin::user()->can('update_'.$this->entity->entity_code)) {
                $form->disableReset();
                $form->disableSubmit();
            }
        });
    }
}
