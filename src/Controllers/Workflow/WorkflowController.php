<?php

namespace Eav\Controllers;

use Eav\Entity;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Eav\Models\Workflow\Workflow;
use Illuminate\Routing\Controller;

class WorkflowController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('eav::eav.Workflow'));
            $content->description(trans('admin.list'));
            $content->body($this->grid()->render());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('eav::eav.Workflow'));
            $content->description(trans('admin.edit'));
            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('eav::eav.Workflow'));
            $content->description(trans('admin.create'));
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Workflow::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->column('type_id',trans('eav::eav.type_id'));
            $grid->column('name',trans('eav::eav.name'));
//            $grid->column('bpmn',trans('eav::eav.bpmn'));
            $grid->column('version',trans('eav::eav.version'));
            $grid->column('user_id',trans('eav::eav.user_id'));
            $grid->created_at(trans('admin.created_at'));
            $grid->updated_at(trans('admin.updated_at'));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        return Admin::form(Workflow::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->select('type_id',trans('eav::eav.type_id'))
                ->options(Entity::all()->pluck('entity_name','id'));//->rules('required')
            $form->text('name',trans('eav::eav.name'))->rules('required');
            $form->mobile('version',trans('eav::eav.version'))->options(['mask' => '9.99.99']);//->rules('required');
            $form->hidden('user_id', trans('task.user_id'))->value(Admin::user()->id);
            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));
            $form->display('bpmn', trans('eav::eav.bpmn'))->with(function($bpmnXML){
                $model = $this;
                return view('eav::admin.form.bpmnViewer',compact('bpmnXML','model'));
            });
//            $form->bpmn('bpmn',trans('eav::eav.bpmn'));//->rules('required');

        });
    }

    public function ajaxBpmnViewer($id)
    {
        $workflow = Workflow::find($id);
        return $workflow->toJson();
    }

    public function ajaxBpmnSave($id)
    {
        $workflow = Workflow::updateOrCreate(['id'=>$id],['bpmn'=>request()->get('bpmn')]);
        return $workflow;
    }
}
