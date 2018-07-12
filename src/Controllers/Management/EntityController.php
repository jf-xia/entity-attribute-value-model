<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use Eav\Attribute;
use Eav\AttributeGroup;
use Eav\AttributeSet;
use Eav\Entity;
use Eav\EntityAttribute;
use Eav\EntityRelation;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class EntityController extends Controller
{
    private $entity;

    public function __construct()
    {
        //todo debug on entity
        $this->entity = (object)['id' => Route::getCurrentRoute()->parameter('entity'),'attributeSet'=>collect()];
    }

    /** todo 2 Report & chartjs setting & default by option group Pie, count Bar & Scatter, value Line, skills Radar & Polar area
     *
     * @return Content
     */
    public function index()
    {
        $content = Admin::content();
        $content->header(trans('eav::eav.entity').trans('eav::eav.list'));
        $content->description('...');
//        $entity = Entity::first();
        //$entity->describe()->pluck('DATA_TYPE','COLUMN_NAME')
//        dd($entity->attributeSet->toArray());
        $content->body(Admin::grid(Entity::class, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('entity_name',trans('eav::eav.entity_name'));
            $grid->column('entity_code',trans('eav::eav.entity_code'));
            $grid->column('entity_class',trans('eav::eav.entity_class'));
            $grid->column('entity_table',trans('eav::eav.entity_table'));
            $grid->column('defaultAttributeSet.attribute_set_name',trans('eav::eav.default_attribute_set_id'));
//            $grid->column('attributeSet',trans('eav::eav.additional_attribute_table'))
//                ->pluck('attribute_set_name')->label();
//            $grid->column('is_flat_enabled',trans('eav::eav.is_flat_enabled'))->display(function($val){return status()[$val];});
            $grid->filter(function ($filter)  {
                $filter->disableIdFilter();
                $filter->like('entity_code',trans('eav::eav.entity_code'));
                $filter->like('entity_class',trans('eav::eav.entity_class'));
                $filter->like('entity_table',trans('eav::eav.entity_table'));
                $filter->equal('is_flat_enabled',trans('eav::eav.is_flat_enabled'))->select(status());
            });
        }));
        return $content;
    }

    /**
     * Create interface. create with permission & menu
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('eav::eav.create').trans('eav::eav.entity'));
            $content->description('...');
            $content->body($this->entityForm());
        });
    }

    public function getDisplayAttrsAjax()
    {
        $q = Input::get('q');
        return Attribute::where('entity_id',$q)->get(['id','frontend_label']);
    }

    public function getOptionsAjax()
    {
        $q = request()->get('q');
        $entityClass = base64_decode(request()->get('entity'));
        $optionsName = request()->get('option');
        return $entityClass::where($optionsName, 'like', "%$q%")->paginate(null, ['id', $optionsName.' as text']);
    }

//    public function getEntity($id = null)
//    {
//        if (!$this->entity) {
//            $id = $id ? : Route::getCurrentRoute()->parameter('entity');
//            $this->entity = Entity::find($id);
//        }
//        return $this->entity;
//    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function  entityForm()
    {
        return Admin::form(Entity::class, function (Form $form) {
//            $form->display('id', 'ID');
            $form->setTitle(trans('eav::eav.entity').trans('eav::eav.edit'));
            $form->text('entity_name',trans('eav::eav.entity_name'));
            $form->text('entity_code',trans('eav::eav.entity_code'));//todo 3 mask a-z_-
            $form->text('entity_class',trans('eav::eav.entity_class'));//->rules('required|unique:entities'); todo 3 set default base code
            $form->text('entity_table',trans('eav::eav.entity_table'));//->rules('required|unique:entities');
            $form->select('default_attribute_set_id',trans('eav::eav.default_attribute_set_id'))
                ->options($this->entity->attributeSet->pluck('attribute_set_name','id'));
            $form->multipleSelect('relation_entity_ids',trans('eav::eav.entity_relations'))->options(Entity::all()->pluck('entity_name','id'));
//            $form->column('additional_attribute_table',trans('eav::eav.additional_attribute_table'));
//            $form->select('is_flat_enabled',trans('eav::eav.is_flat_enabled'))->options(status()); //todo 3 flat table
//            $form->subForm('attributes_form',trans('eav::eav.attributes'), function (Form\NestedForm $form) {
//                (new \Eav\Controllers\AttributeController)->formFileds($form);
//            });
//            $form->subForm('entity_relations',trans('eav::eav.entity_relations'), function (Form\NestedForm $form) {
//                $form->select('relation_type',trans('eav::eav.relation_type'))->options(EntityRelation::relationTypeOption());
//                $form->select('relation_entity_id',trans('eav::eav.relation_entity_id'))
//                    ->options(Entity::all()->pluck('entity_name','id'))
//                    ->load('display_attr_id',admin_url('entity/ajax/attrs'),'id','frontend_label');
//                $form->select('display_attr_id',trans('eav::eav.display_attr_id'))
//                    ->options(function ($id) {
//                        return ($attr = Attribute::find($id)) ? (Attribute::where('entity_id',$attr->entity_id)
//                            ->pluck('frontend_label','id')->union([0=>'(Null)'])) : [0=>'(Null)'];
//                    });
//            });
            $form->setWidth(8,3);
            $form->builder()->addHiddenField((new Form\Field\Hidden('_previous_'))->value(route('entity.edit',$this->entity->id)));
            $this->formOnSave($form);
        });
    }

    public function formOnSave($form)
    {
        $form->saving(function($form){
            //($form->model()); todo 3 change it in Action Button
            if (!class_exists(Input::get('entity_class'))){
                \Artisan::call('eav:make:entity',[
                    'name'=>Input::get('entity_code'),
                    'class'=>Input::get('entity_class'),
//                        '--path'=>'app/Models/Eav', //todo 3 Models path change
                ]);
                \Artisan::call('migrate');
            }
        });
        $form->saved(function($form){
            //create with ModelMakeCommand & attrs & m2m & set default attrSet where create new attr
            if (!$form->model()->default_attribute_set_id){
                $this->runSetEntityData($form);
            }
            if (empty(Role::where('slug',$form->model()->entity_code.'_leader')->first())){
                $this->runSetAdminData($form);
            }
        });
    }

    /**
     * create entity with rbac data
     *
     * @param $form
     */
    public function runSetAdminData($form)
    {
        //todo custom setting
        $model = $form->model();
        $roleLeader = Role::create(['name' => $model->entity_name.'Leader', 'slug' => $model->entity_code.'_leader']);
        $roleBase = Role::create(['name' => $model->entity_name.'Base', 'slug' => $model->entity_code.'_base']);
        $roleRelation = Role::create(['name' => $model->entity_name.'Relation', 'slug' => $model->entity_code.'_relation']);
        Administrator::first()->roles()->save($roleLeader);
        $en = $model->entity_name;
        $ec = $model->entity_code;
        $permsList=Permission::create(['name'=>trans('eav::eav.list').$en,'slug'=>'list_'.$ec,'http_method'=>['GET'],'http_path'=>"/".$ec]);
        $permsView=Permission::create(['name'=>trans('eav::eav.view').$en,'slug'=>'view_'.$ec,'http_method'=>['GET'],'http_path'=>"/".$ec."/*"]);
        $permsCreate=Permission::create(['name'=>trans('eav::eav.create').$en,'slug'=>'create_'.$ec,'http_method'=>['POST'],'http_path'=>"/".$ec."/*"]);
        $permsEdit=Permission::create(['name'=>trans('eav::eav.edit').$en,'slug'=>'update_'.$ec,'http_method'=>['PUT','PATCH'],'http_path'=>"/".$ec."/*"]);
        $permsDelete=Permission::create(['name'=>trans('eav::eav.delete').$en,'slug'=>'delete_'.$ec,'http_method'=>['DELETE'],'http_path'=>"/".$ec."/*"]);
        $permsExport=Permission::create(['name'=>trans('eav::eav.export').$en,'slug'=>'export_'.$ec]);
        $roleLeader->permissions()->saveMany([$permsList,$permsView,$permsCreate,$permsEdit,$permsDelete,$permsExport]);
        $roleBase->permissions()->saveMany([$permsList,$permsView,$permsEdit]);
        $roleRelation->permissions()->saveMany([$permsList,$permsView]);
        $menu = Menu::create(['parent_id'=>1,'order'=>$model->id,'title'=>$en,'icon'=>'fa-puzzle-piece','uri'=>'/'.$ec]);
        $menu->roles()->saveMany([$roleLeader,$roleBase,$roleRelation]);
    }

    /**
     * create entity with Attributes & set & group data
     *
     * @param $form
     */
    public function runSetEntityData($form)
    {
        $attributeSet = AttributeSet::create(['entity_id'=>$form->model()->id,'attribute_set_name'=>'基本']);
        $form->model()->attribute_set_id = $attributeSet->id;
        $form->model()->save();
        $attributeGroup = AttributeGroup::create(
            ['attribute_set_id'=>$attributeSet->id,'attribute_group_name'=>'基本','order'=>0]);
        Attribute::insert([
            ['entity_id'=>$attributeSet->entity_id, 'attribute_code'=>'created_at', 'backend_type'=>'static',
                'frontend_type'=>'datetime', 'frontend_label'=>trans('eav::eav.created_at'), 'is_filterable'=>1,'order'=>999],
            ['entity_id'=>$attributeSet->entity_id, 'attribute_code'=>'updated_at', 'backend_type'=>'static',
                'frontend_type'=>'datetime', 'frontend_label'=>trans('eav::eav.updated_at'), 'is_filterable'=>1,'order'=>999]
        ]);
        $insertEAs = [];
        foreach (Attribute::where('entity_id', $attributeSet->entity_id)->get() as $attr) {
            $insertEAs[] = ['entity_id'=>$attributeSet->entity_id, 'attribute_set_id'=>$attributeSet->id,
                'attribute_group_id'=>$attributeGroup->id, 'attribute_id'=>$attr->id,];
        }
        EntityAttribute::insert($insertEAs);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->entity = Entity::find($id);
        $content = Admin::content();
        $content->header($this->entity->entity_name.' & '.trans('eav::eav.attributes').trans('eav::eav.edit'));
        $content->description('...');

        $content->row(function (Row $row) {
            $row->column(4, function (Column $column) {
                $column->append($this->entityForm()->edit($this->entity->id));
            });
            $row->column(4, function (Column $column) {
                $column->append((new Box(trans('eav::eav.attribute_set').trans('eav::eav.list'),
                    '<a href="'.admin_base_path('entity').'/'.$this->entity->id.
                    '/edit" class="btn btn-sm btn-success"><i class="fa fa-save"></i>&nbsp;&nbsp;新增</a>'.
                    $this->attrSetGrid()))->style('success'));
            });
            $row->column(4, function (Column $column) {
                $column->append($this->attrSetForm());
            });
        });
        $content->row(function (Row $row) {
            $row->column(8, function (Column $column) {
                $attrSet = $this->entity->attributeSet->find(Input::get('set'));
                $attribute_set_name = $attrSet ? $attrSet->attribute_set_name : '';
                $column->append((new Box($attribute_set_name.trans('eav::eav.attributes').trans('eav::eav.edit'),$this->attrGrid())));
            });
            $row->column(4, function (Column $column) {
                $column->append($this->attrForm());
            });
        });
        return $content;
    }

    public function attrMap()
    {
        $inputs = Input::all();
        $attributeSet = AttributeSet::query()->find(Input::get('set'));
        $entityId = $attributeSet ? $attributeSet->entity_id : null;
        $setId = $inputs['set'];
        unset($inputs['_token'],$inputs['set']);
        if ($inputs){
            try{
                foreach ($inputs as $input) {
                    if (isset($input['original']['attribute_group_id'])) {
                        EntityAttribute::query()->where('attribute_group_id',$input['original']['attribute_group_id'])
                            ->where('attribute_id',$input['attribute_id'])->update([
                                'attribute_group_id' => $input['attribute_group_id']
                            ]);
                    } else {
                        if (isset($input['attribute_group_id']) && $this->entity->id){
                            EntityAttribute::query()->create([
                                'entity_id' => $this->entity->id,
                                'attribute_set_id' => $setId,
                                'attribute_group_id' => $input['attribute_group_id'],
                                'attribute_id' => $input['attribute_id']
                            ]);
                        }
                    }
                }
                admin_toastr(trans('admin.save_succeeded'));
                return redirect(url(admin_base_path('entity').'/'.$entityId.'/edit?set='.$setId));
            }catch (\Exception $e){
                \Log::debug($e);
            }
        }
        admin_toastr(trans('admin.save').trans('admin.failed'),'error');
        return redirect(url(admin_base_path('entity').'/'.$entityId));
    }

    public function selectAttr($name,$default,$options,$attrId)
    {
        $attribute = new \Encore\Admin\Form\Field\Select('');
        $attribute->setElementName('attr'.$attrId.'['.$name.']');
        $attribute->default($default);
        $attribute->setWidth(12,0);
        $attribute->options($options);
        $original = $default ? '<input name="attr'.$attrId.'[original]['.$name.']" type="hidden" value="'.$default.'" />':'';
        return $attribute.$original;
    }

    public function attrData($rows)
    {
        $drows = [];
        $attrSet = Input::get('set')?Input::get('set'):$this->entity->default_attribute_set_id;
        $entityAttr = EntityAttribute::where('attribute_set_id', $attrSet)->get();
        $optionAttributeGroup = AttributeGroup::where('attribute_set_id',$attrSet)->pluck('attribute_group_name','id');
        foreach ($rows as $row) {
            $drow=[];
            $default_attr_group_id = $entityAttr->where('attribute_id',$row['id'])->first()->attribute_group_id ?? '';
            $drow['attribute_group_id']=$this->selectAttr('attribute_group_id',$default_attr_group_id,$optionAttributeGroup,$row['id']);
            $drow['attribute_code'] = $row['attribute_code'].
                '<input name="attr'.$row['id'].'[attribute_id]" type="hidden" value="'.$row['id'].'" />';
            $drow['frontend_label'] = $row['frontend_label'];
            $drow['frontend_type'] = $row['frontend_type'];
//            $drow['order'] = $row['order'];
            $permisUrl = url(admin_base_path('entity').'/'.$this->entity->entity_code).'/attr/'.$row['id'].'/permission/'.
                            $row['attribute_code'].'/name/'.$row['frontend_label'];
            $editUrl = url(admin_base_path('entity').'/'.$this->entity->id).'/edit?set='.Input::get('set').'&attr='.$row['id'];
            $delUrl = url(admin_base_path('entity/'.$this->entity->id.'/attr')). '/'.$row['id'];
            $delJs = 'if(confirm(\'确认删除吗\')){window.location.href=\''.$delUrl.'\';}';
            $drow['action']='<a href="'.$permisUrl.'" target="_blank" ><i class="fa fa-ban"></i></a> '.
                            '<a href="'.$editUrl.'"><i class="fa fa-edit"></i></a> '.
                            '<a onclick="'.$delJs.'" href="javascript:void(0);"><i class="fa fa-trash"></i></a>';
            $drows[] = $drow;
        }
        return $drows;
    }

    /**
     * note to user Permission need set to Role or User by Manual
     * @param $enitiyCode
     * @param $attrId
     * @param $attrCode
     * @param $attrLabel
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function attrPermission($entityCode, $attrId, $attrCode, $attrLabel)
    {

        $roleLeader = Role::where('slug', $entityCode.'_leader')->orWhere('name', $entityCode.'Leader')->first();
        $roleBase = Role::where('slug', $entityCode.'_base')->orWhere('name', $entityCode.'Base')->first();
        if (empty($roleLeader)) $roleLeader = Role::create(['name' => $entityCode.'Leader', 'slug' => $entityCode.'_leader']);
        if (empty($roleBase)) $roleBase = Role::create(['name' => $entityCode.'Base', 'slug' => $entityCode.'_base']);

        $hasLViewPermission = $roleLeader->permissions->where('slug',$entityCode.'_view_'.$attrCode.'_'.$attrId)->count();
        $hasBViewPermission = $roleBase->permissions->where('slug',$entityCode.'_view_'.$attrCode.'_'.$attrId)->count();
        $hasEditPermission = $roleLeader->permissions->where('slug',$entityCode.'_edit_'.$attrCode.'_'.$attrId)->count();

        $permsViewAttr=Permission::updateOrCreate(
            ['slug'=>$entityCode.'_view_'.$attrCode.'_'.$attrId],[
            'name'=>trans('eav::eav.view').trans('eav::eav.attribute').':'.$attrLabel.'-'.$entityCode,
            'slug'=>$entityCode.'_view_'.$attrCode.'_'.$attrId
        ]);
        $permsEditAttr=Permission::updateOrCreate(
            ['slug'=>$entityCode.'_edit_'.$attrCode.'_'.$attrId],[
            'name'=>trans('eav::eav.edit').trans('eav::eav.attribute').':'.$attrLabel.'-'.$entityCode,
            'slug'=>$entityCode.'_edit_'.$attrCode.'_'.$attrId
        ]);
        if (!$hasEditPermission) $roleLeader->permissions()->save($permsEditAttr);
        if (!$hasLViewPermission) $roleLeader->permissions()->save($permsViewAttr);
        if (!$hasBViewPermission) $roleBase->permissions()->save($permsViewAttr);

        admin_toastr(trans('eav::eav.default').trans('admin.update_succeeded').
            '! 默认配置Leader角色可读写，Base角色可读，其他用户不可读写，提示：您也可以手动设置'.$attrLabel.'属性读写权限!');
        return redirect(route('roles.edit',$roleLeader->id));
    }

    public function attrGrid()
    {
        $form = '';
        $grid = new \Encore\Admin\Widgets\Table();
        $attrs = Attribute::query()->where('entity_id',$this->entity->id)->get();
        if ($attrs){
            $drows = $this->attrData($attrs->toArray());
            $grid->setHeaders(array_map(function($th){return trans('eav::eav.'.$th);},array_keys($drows[0])));
            $grid->setRows($drows);
        }
        $setId = (new Form\Field\Hidden('set'))->value(Input::get('set')?Input::get('set'):$this->entity->default_attribute_set_id);
        $form .= '<form action="'.admin_base_path('entity/'.$this->entity->id.'/attr/setmap').'" method="post" accept-charset="UTF-8">';
        $form .= $grid->render();
        $form .= '<div class="box-footer">'.$setId.csrf_field().'<div class="col-md-2"></div><div class="col-md-8">
        <div class="btn-group pull-right"><button type="submit" class="btn btn-info pull-right" >'.trans('eav::eav.save').'</button></div>
        <div class="btn-group pull-left"><button type="reset" class="btn btn-warning">'.trans('eav::eav.reset').'</button></div>
        </div></div></form>';
        return $form;
    }

    public function attrSetGrid()
    {
        //todo 3 table extend
        //todo set table height
        $grid = new \Encore\Admin\Widgets\Table();
        $rows = AttributeSet::with('entity')->where('entity_id',$this->entity->id)->get()->toArray();
        if ($rows){
            foreach ($rows as &$row) {
                $row['action']='<a href="'.url(admin_base_path('entity').'/'.$this->entity->id).'/edit?set='.$row['id'].'"><i class="fa fa-edit"></i></a>'.
                    ' <a onclick="if(confirm(\'确认删除吗\')){window.location.href=\''.url(admin_base_path('entity/'.$this->entity->id.'/attr/set')).
                    '/'.$row['id'].'\';}" href="javascript:void(0);"><i class="fa fa-trash"></i></a>';
                $row['entity_id']=$row['entity']['entity_name'];
                unset($row['id']);
                unset($row['entity']);
            }
            unset($row);
            $grid->setHeaders(array_map(function($th){return trans('eav::eav.'.$th);},array_keys($rows[0])));
            $grid->setRows($rows);
        }
        return $grid->render();
    }

    public function attrSetForm()
    {
        $form = Admin::form(AttributeSet::class,function (Form $form) {
            $form->setAction(admin_base_path('entity/'.$this->entity->id.'/attr/set'));
            $form->hidden('entity_id', trans('eav::eav.entity'))->setElementClass('dd')->value($this->entity?$this->entity->id:null);
            $form->text('attribute_set_name', trans('eav::eav.attribute_set_name'))->rules('required');
            $form->subForm('attribute_group',trans('eav::eav.attribute_group'), function (Form\NestedForm $form) {
                $form->text('attribute_group_name', trans('eav::eav.attribute_group_name'))->rules('required');
                $form->text('order', trans('eav::eav.order'))->rules('required');
            });
            $form->builder()->addHiddenField((new Form\Field\Hidden('_previous_'))->value(request()->getRequestUri()));
            if (Input::get('set')) $form->builder()->addHiddenField((new Form\Field\Hidden('set'))->value(Input::get('set')));
            $form->builder()->getTools()->disableListButton();
            $form->builder()->getTools()->disableBackButton();
        });
        if (Input::get('set')) {
            $form->edit(Input::get('set'));
        }
        $form->setWidth(8,3);
        return $form;
    }

    protected function attrForm()
    {
        return Admin::form(Attribute::class, function (Form $form) {
            $form->setAction(admin_base_path('entity/'.$this->entity->id.'/attr'));
            $form->hidden('entity_id',trans('eav::eav.entity'))->value($this->entity->id);
            $this->formFileds($form);
            $form->subForm('option',trans('eav::eav.option'), function (Form\NestedForm $form) {
                $form->text('label',trans('eav::eav.label'))->setElementClass('option_label');
                $form->text('value',trans('eav::eav.value'));
            });
            $form->builder()->addHiddenField((new Form\Field\Hidden('_previous_'))->value(request()->getRequestUri()));
            if (Input::get('attr')) $form->builder()->addHiddenField((new Form\Field\Hidden('attr'))->value(Input::get('attr')));
            $form->setWidth(8,3);
            if (Input::get('attr')) {
                $form->edit(Input::get('attr'));
            }
        });
    }

    public function formFileds($form)
    {
        $form->text('attribute_code',trans('eav::eav.attribute_code'));//->rules('unique:attributes');
//        $form->text('backend_class',trans('eav::eav.backend_class'));
        $form->select('backend_type',trans('eav::eav.backend_type'))->options(Attribute::backendType())->rules('required');
//        $form->text('backend_table',trans('eav::eav.backend_table'));
//        $form->text('frontend_class',trans('eav::eav.frontend_class'));
        $form->select('frontend_type',trans('eav::eav.frontend_type'))->options(Attribute::frontendType())->rules('required');
        $form->text('frontend_label',trans('eav::eav.frontend_label'));
//        $form->text('source_class',trans('eav::eav.source_class'));
        $form->text('default_value',trans('eav::eav.default_value'));
        $form->select('not_list',trans('eav::eav.not_list'))->options(status())->rules('required');
        $form->select('not_report',trans('eav::eav.not_report'))->options(status())->rules('required');
        $form->select('is_unique',trans('eav::eav.is_unique'))->options(status())->rules('required');
        $form->select('is_filterable',trans('eav::eav.is_filterable'))->options(status())->rules('required');
        $form->select('is_searchable',trans('eav::eav.is_searchable'))->options(status())->rules('required');
        $form->select('is_required',trans('eav::eav.is_required'))->options(status())->rules('required');
        $form->text('required_validate_class',trans('eav::eav.required_validate_class'));
        $form->text('order',trans('eav::eav.order'));
        $form->text('list_field_html',trans('eav::eav.list_field_html'));
        //todo 3 form_field_html form
        $form->text('help',trans('eav::eav.help'));
        $form->text('placeholder',trans('eav::eav.placeholder'));
    }

    public function attrStore()
    {
        $id = Input::get('_method')=='PUT' ? Input::get('attr') : '';
        if ($id) {
            $this->attrForm()->update($id);
        } else {
            $this->attrForm()->store();
        }
        return redirect(url(Input::get('_previous_')));
    }

    public function attrDelete($entityId,$attrId)
    {
        $attr = Attribute::find($attrId);
        if ($attr && $attr->delete()) {
            admin_toastr(trans('admin.delete_succeeded'));
            return redirect(route('entity.edit',$attr->entity_id));
        } else {
            admin_toastr(trans('admin.delete_failed'),'error');
        }
        return redirect(route('entity.index'));
    }

    public function attrSetStore()
    {
        $id = Input::get('_method')=='PUT' ? Input::get('set') : '';
        if ($id) {
            $this->attrSetForm()->update($id);
        } else {
            $this->attrSetForm()->store();
        }
        return redirect(url(Input::get('_previous_')));
    }

    public function attrSetDelete($id)
    {
        $attrSet = AttributeSet::find($id);
        if ($attrSet && $attrSet->delete()) {
            admin_toastr(trans('admin.delete_succeeded'));
            return redirect(route('entity.edit',$attrSet->entity_id));
        } else {
            admin_toastr(trans('admin.delete_failed'),'error');
        }
        return redirect(route('entity.index'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        return $this->entityForm()->update($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($this->entityForm()->destroy($id)) {
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        return $this->entityForm()->store();
    }

    public function show($id)
    {
        return redirect(route('entity.edit',$id));
    }
}
