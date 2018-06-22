<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use Eav\Attribute;
use Eav\AttributeGroup;
use Eav\AttributeSet;
use Eav\Entity;
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
            });

            $row->column(4, function (Column $column) {
                $column->append((new Box(trans('eav::eav.attribute_group'),
                    '<a href="'.admin_base_path('attributeset').'?set='.Input::get('set').
                    '" class="btn btn-sm btn-success"><i class="fa fa-save"></i>&nbsp;&nbsp;新增</a>'.
                    $this->attrGroupGrid().$this->attrGroupForm()))->style('success'));
            });
            $row->column(4, '');
        });
        return $content;
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
