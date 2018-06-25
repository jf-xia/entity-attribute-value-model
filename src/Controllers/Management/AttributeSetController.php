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
        $content = Admin::content();
        $content->header(trans('eav::eav.attributes').trans('eav::eav.list'));
        $content->description('...');

        $content->row(function (Row $row) {
            $row->column(4, function (Column $column) {
                $column->append((new Box(trans('eav::eav.attribute_set'),
                    '<a href="'.admin_base_path('attributeset').
                    '" class="btn btn-sm btn-success"><i class="fa fa-save"></i>&nbsp;&nbsp;新增</a>'.
                    $this->attrSetGrid().$this->attrSetForm()))->style('success'));
                $column->append((new Box(trans('eav::eav.attribute_group'),
                    '<a href="'.admin_base_path('attributeset').'?set='.Input::get('set').
                    '" class="btn btn-sm btn-success"><i class="fa fa-save"></i>&nbsp;&nbsp;新增</a>'.
                    $this->attrGroupGrid().$this->attrGroupForm()))->style('success'));
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
            $save = null;
            foreach ($inputs as $input) {
                if ($input['entity_id'] && $input['attribute_set_id'] && $input['attribute_group_id'] && $input['attribute_id']){
                    $original = ['attribute_id' => $input['attribute_id']];
                    if (isset($input['original']['attribute_group_id'])) {
                        $original['attribute_group_id']=$input['original']['attribute_group_id'];
                        $save = EntityAttribute::query()->updateOrCreate($original,[
                            'attribute_group_id' => $input['attribute_group_id'],
                            'attribute_id' => $input['attribute_id']
                        ])->save();
                    } else {
                        $save = EntityAttribute::query()->create([
                            'entity_id' => $this->getEntityId(),
                            'attribute_set_id' => $setId,
                            'attribute_group_id' => $input['attribute_group_id'],
                            'attribute_id' => $input['attribute_id']
                        ])->save();
                    }
                }
            }
            if ($save){
                admin_toastr(trans('admin.save_succeeded'));
                return redirect(url(admin_base_path('attributeset').'?set='.$setId));
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

    public function attrData($rows,$rows2)
    {
        $drows = [];
        $optionEntity = Entity::all()->pluck('entity_name','entity_id');
        $optionAttributeSet = AttributeSet::all()->pluck('attribute_set_name','attribute_set_id');
        $optionAttributeGroup = AttributeGroup::all()->pluck('attribute_group_name','attribute_group_id');
        foreach ($rows as $row) {
            $drow=[];
            $drow['entity_id']=$this->selectAttr('entity_id',$row['entity_id'],$optionEntity,$row['attribute']['attribute_id']);
            $drow['attribute_set_id']=$this->selectAttr('attribute_set_id',$row['attribute_set_id'],$optionAttributeSet,$row['attribute']['attribute_id']);
            $drow['attribute_group_id']=$this->selectAttr('attribute_group_id',$row['attribute_group_id'],$optionAttributeGroup,$row['attribute']['attribute_id']);
            $drow['attribute_code'] = $row['attribute']['attribute_code'].
                '<input name="attr'.$row['attribute_id'].'[attribute_id]" type="hidden" value="'.$row['attribute']['attribute_id'].'" />';
            $drow['frontend_label'] = $row['attribute']['frontend_label'];
            $drow['frontend_type'] = $row['attribute']['frontend_type'];
//                $drow['frontend_class'] = $row['attribute']['frontend_class'];
            $drows[] = $drow;
        }
        foreach ($rows2 as $row) {
            $drow=[];
            $drow['entity_id']=$this->selectAttr('entity_id','',$optionEntity,$row['attribute_id']);
            $drow['attribute_set_id']=$this->selectAttr('attribute_set_id','',$optionAttributeSet,$row['attribute_id']);
            $drow['attribute_group_id']=$this->selectAttr('attribute_group_id','',$optionAttributeGroup,$row['attribute_id']);
            $drow['attribute_code'] = $row['attribute_code'].
                '<input name="attr'.$row['attribute_id'].'[attribute_id]" type="hidden" value="'.$row['attribute_id'].'" />';
            $drow['frontend_label'] = $row['frontend_label'];
            $drow['frontend_type'] = $row['frontend_type'];
            $drows[] = $drow;
        }
        return $drows;
    }

    public function attrGrid()
    {
        $grid = new \Encore\Admin\Widgets\Table();
        if(Input::get('set')){
            $entityAttr = EntityAttribute::with('attribute')->where('attribute_set_id', Input::get('set'))->get();
            $attrs = Attribute::query()->where('entity_id',$this->getEntityId())
                ->whereNotIn('attribute_id',$entityAttr->pluck('attribute_id'))->get();
            if ($entityAttr || $attrs){
                $drows = $this->attrData($entityAttr->toArray(),$attrs->toArray());
                $grid->setHeaders(array_map(function($th){return trans('eav::eav.'.$th);},array_keys($drows[0])));
                $grid->setRows($drows);
            }
        }
        $setId = (new Form\Field\Hidden('set'))->value(Input::get('set')?Input::get('set'):'');
        $form = '<form action="'.admin_base_path('attr/setmap').'" method="post" accept-charset="UTF-8">';
        $form .= $grid->render();
        $form .= '<div class="box-footer">'.$setId.csrf_field().'<div class="col-md-2"></div><div class="col-md-8">
        <div class="btn-group pull-right"><button type="submit" class="btn btn-info pull-right" >'.trans('eav::eav.save').'</button></div>
        <div class="btn-group pull-left"><button type="reset" class="btn btn-warning">'.trans('eav::eav.reset').'</button></div>
        </div></div></form>';
        return $form;
    }

    public function attrGroupGrid()
    {
        $grid = new \Encore\Admin\Widgets\Table();
        $rows = AttributeGroup::with('attribute_set')->get()->toArray();
        if ($rows){
            foreach ($rows as &$row) {
                $row['action']='<a href="'.url(admin_base_path('attributeset')).'?set='.Input::get('set').'&group='.$row['attribute_group_id'].'"><i class="fa fa-edit"></i></a>'.' <a href="'.url(admin_base_path('attr/group')).'/'.$row['attribute_group_id'].'"><i class="fa fa-trash"></i></a>';
                $row['attribute_set_id']=$row['attribute_set']['attribute_set_name'];
                unset($row['attribute_group_id']);
                unset($row['attribute_set']);
            }
            unset($row);
            $grid->setHeaders(array_map(function($th){return trans('eav::eav.'.$th);},array_keys($rows[0])));
            $grid->setRows($rows);
        }
        return $grid->render();
    }

    public function attrGroupForm()
    {
        $form = Admin::form(AttributeGroup::class,function (Form $form) {
            $form->setAction(admin_base_path('attr/group'));
            $form->display('attribute_group_id', 'ID');
            $form->select('attribute_set_id', trans('eav::eav.attribute_set_id'))->rules('required')
                ->options(AttributeSet::all()->pluck('attribute_set_name','attribute_set_id'))
                ->default(Input::get('set'));
            $form->text('attribute_group_name', trans('eav::eav.attribute_group_name'))->rules('required');
            $form->number('order', trans('eav::eav.order'))->rules('required');
            $form->builder()->addHiddenField((new Form\Field\Hidden('_previous_'))->value(Request::getRequestUri()));
            $form->builder()->addHiddenField((new Form\Field\Hidden('group'))->value(Input::get('group')?Input::get('group'):''));
            $form->builder()->getTools()->disableListButton();
            $form->builder()->getTools()->disableBackButton();
        });
        if (Input::get('group')) {
            $form->edit(Input::get('group'));
        }
        return $form;
    }

    public function attrSetGrid()
    {
        $grid = new \Encore\Admin\Widgets\Table();
        $rows = AttributeSet::with('entity')->get()->toArray();
        if ($rows){
            foreach ($rows as &$row) {
                $row['action']='<a href="'.url(admin_base_path('attributeset')).'?set='.$row['attribute_set_id'].'"><i class="fa fa-edit"></i></a>'.' <a href="'.url(admin_base_path('attr/set')).'/'.$row['attribute_set_id'].'"><i class="fa fa-trash"></i></a>';
                $row['entity_id']=$row['entity']['entity_name'];
                unset($row['attribute_set_id']);
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
            $form->display('attribute_set_id', 'ID');
            $form->select('entity_id', trans('eav::eav.entity'))->rules('required')
                ->options(Entity::all()->pluck('entity_name','entity_id'));
            $form->text('attribute_set_name', trans('eav::eav.attribute_set_name'))->rules('required');
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
        if ($id){
            $this->attrSetForm()->update($id);
        } else {
            $this->attrSetForm()->store();
        }
        return redirect(url(admin_base_path('attributeset')).'?set='.Input::get('set').'&group='.Input::get('group'));
    }

    public function attrGroupStore()
    {
        $id = Input::get('_method')=='PUT' ? Input::get('group') : '';
        if ($id){
            $this->attrGroupForm()->update($id);
        } else {
            $this->attrGroupForm()->store();
        }
        return redirect(url(admin_base_path('attributeset')).'?set='.Input::get('set').'&group='.Input::get('group'));
    }

    public function attrGroupDelete($id)
    {
        $this->destroy($this->attrGroupForm(),$id);
    }

    public function attrSetDelete($id)
    {
        $this->destroy($this->attrSetForm(),$id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($form,$id)
    {
        if ($form->destroy($id)) {
            admin_toastr(trans('admin.delete_succeeded'));
        } else {
            admin_toastr(trans('admin.delete_failed'),'error');
        }
        return redirect(url(admin_base_path('attributeset')));
    }
}