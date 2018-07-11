<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use Eav\Attribute;
use Eav\AttributeGroup;
use Eav\AttributeSet;
use Eav\Entity;
use Eav\EntityAttribute;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

class AttributeSetController extends Controller
{
    use ModelForm;
    public function index()
    {
        //todo 3 reconstruction AttributeSetController to AttributeController
        $content = Admin::content();
        $content->header(trans('eav::eav.attributes').trans('eav::eav.list'));
        $content->description('...');

        $content->row(function (Row $row) {
            $row->column(4, function (Column $column) {
                $column->append((new Box(trans('eav::eav.attribute_set'),
                    '<a href="'.admin_base_path('attributeset').
                    '" class="btn btn-sm btn-success"><i class="fa fa-save"></i>&nbsp;&nbsp;新增</a>'.
                    $this->attrSetGrid().$this->attrSetForm()))->style('success'));
//                $column->append((new Box(trans('eav::eav.attribute_group'),
//                    '<a href="'.admin_base_path('attributeset').'?set='.Input::get('set').
//                    '" class="btn btn-sm btn-success"><i class="fa fa-save"></i>&nbsp;&nbsp;新增</a>'.
//                    $this->attrGroupGrid().$this->attrGroupForm()))->style('success'));
            });

            $row->column(8, function (Column $column) {
                $attrEntityId = $this->getEntityId();
                $editAble = $attrEntityId ? '<a href="'.admin_base_path('entity').'/'.$attrEntityId.
                    '/edit" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i>&nbsp;&nbsp;'.
                    trans('eav::eav.edit').trans('eav::eav.attributes').trans('eav::eav.list').'</a>' : '';
                $column->append((new Box(trans('eav::eav.attributes'),$editAble.$this->attrGrid())));
            });
        });
        return $content;
    }

    public function getEntityId()
    {
        $attributeSet = AttributeSet::query()->find(Input::get('set'));
        return $attributeSet ? $attributeSet->entity_id : null;
    }

    public function attrMap()
    {
        $inputs = Input::all();
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
                        if (isset($input['attribute_group_id']) && $this->getEntityId()){
                            EntityAttribute::query()->create([
                                'entity_id' => $this->getEntityId(),
                                'attribute_set_id' => $setId,
                                'attribute_group_id' => $input['attribute_group_id'],
                                'attribute_id' => $input['attribute_id']
                            ]);
                        }
                    }
                }
                admin_toastr(trans('admin.save_succeeded'));
                return redirect(url(admin_base_path('attributeset').'?set='.$setId));
            }catch (\Exception $e){
                \Log::debug($e);
            }
        }
        admin_toastr(trans('admin.save').trans('admin.failed'),'error');
        return redirect(url(admin_base_path('attributeset')));
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
        $entityAttr = EntityAttribute::where('attribute_set_id', Input::get('set'))->get();
        $optionAttributeGroup = AttributeGroup::where('attribute_set_id',Input::get('set'))->pluck('attribute_group_name','id');
        foreach ($rows as $row) {
            $drow=[];
            $default_attr_group_id = $entityAttr->where('attribute_id',$row['id'])->first()->attribute_group_id ?? '';
            $drow['attribute_group_id']=$this->selectAttr('attribute_group_id',$default_attr_group_id,$optionAttributeGroup,$row['id']);
            $drow['attribute_code'] = $row['attribute_code'].
                '<input name="attr'.$row['id'].'[attribute_id]" type="hidden" value="'.$row['id'].'" />';
            $drow['frontend_label'] = $row['frontend_label'];
            $drow['frontend_type'] = $row['frontend_type'];
            $drow['order'] = $row['order'];
            $drows[] = $drow;
        }
        return $drows;
    }

    public function attrGrid()
    {
        $form = '';
        if (Input::get('set')) {
            $grid = new \Encore\Admin\Widgets\Table();
            $attrs = Attribute::query()->where('entity_id',$this->getEntityId())->get();
//            ->whereNotIn('attribute_id',$entityAttr->pluck('attribute_id'))
            if ($attrs){
                $drows = $this->attrData($attrs->toArray());
                $grid->setHeaders(array_map(function($th){return trans('eav::eav.'.$th);},array_keys($drows[0])));
                $grid->setRows($drows);
            }
            $setId = (new Form\Field\Hidden('set'))->value(Input::get('set')?Input::get('set'):'');
            $form .= '<form action="'.admin_base_path('attr/setmap').'" method="post" accept-charset="UTF-8">';
            $form .= $grid->render();
            $form .= '<div class="box-footer">'.$setId.csrf_field().'<div class="col-md-2"></div><div class="col-md-8">
            <div class="btn-group pull-right"><button type="submit" class="btn btn-info pull-right" >'.trans('eav::eav.save').'</button></div>
            <div class="btn-group pull-left"><button type="reset" class="btn btn-warning">'.trans('eav::eav.reset').'</button></div>
            </div></div></form>';
        }
        return $form;
    }

    public function attrSetGrid()
    {
        //todo 3 table extend
        $grid = new \Encore\Admin\Widgets\Table();
        $rows = AttributeSet::with('entity')->get()->toArray();
        if ($rows){
            foreach ($rows as &$row) {
                $row['action']='<a href="'.url(admin_base_path('attributeset')).'?set='.$row['id'].'"><i class="fa fa-edit"></i></a>'.
                    ' <a onclick="if(confirm(\'确认删除吗\')){window.location.href=\''.url(admin_base_path('attr/set')).
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
            $form->setAction(admin_base_path('attr/set'));
            $form->display('id', 'ID');
            $form->select('entity_id', trans('eav::eav.entity'))->rules('required')
                ->options(Entity::all()->pluck('entity_name','id'));
            $form->text('attribute_set_name', trans('eav::eav.attribute_set_name'))->rules('required');
            $form->subForm('attribute_group',trans('eav::eav.attribute_group'), function (Form\NestedForm $form) {
//                $form->display('attribute_group_id', 'ID');
                $form->text('attribute_group_name', trans('eav::eav.attribute_group_name'))->rules('required');
                $form->text('order', trans('eav::eav.order'))->rules('required');
            });
            $form->builder()->addHiddenField((new Form\Field\Hidden('_previous_'))->value(Request::getRequestUri()));
            $form->builder()->addHiddenField((new Form\Field\Hidden('set'))->value(Input::get('set')?Input::get('set'):''));
            $form->builder()->getTools()->disableListButton();
            $form->builder()->getTools()->disableBackButton();
        });
        if (Input::get('set')) {
            $form->edit(Input::get('set'));
        }
        return $form;
    }

    public function attrSetStore()
    {
        $id = Input::get('_method')=='PUT' ? Input::get('set') : '';
        if ($id) {
            $this->attrSetForm()->update($id);
        } else {
            $this->attrSetForm()->store();
        }
        return redirect(url(admin_base_path('attributeset')).'?set='.Input::get('set').'&group='.Input::get('group'));
    }

    public function attrSetDelete($id)
    {
        $attrSet = AttributeSet::find($id);
        if ($attrSet && $attrSet->delete()) {
            admin_toastr(trans('admin.delete_succeeded'));
        } else {
            admin_toastr(trans('admin.delete_failed'),'error');
        }
        return redirect(url(admin_base_path('attributeset')));
    }

    public function show($id)
    {
        return redirect(route('entity.edit',$id));
    }
}
