<?php

namespace Eav\Controllers;

use Eav\Entity;
use Eav\Models\Workflow\Workflow;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Eav\Models\Workflow\Process;
use Illuminate\Routing\Controller;

class ProcessController extends Controller
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
            $content->header(trans('eav::eav.Process'));
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
            $content->header(trans('eav::eav.Process'));
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
            $content->header(trans('eav::eav.Process'));
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
        return Admin::grid(Process::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->column('workflow_id',trans('eav::eav.workflow_id'));
            $grid->column('entity_id',trans('eav::eav.entity_id'));
            $grid->column('user_id',trans('eav::eav.user_id'));
            $grid->column('title',trans('eav::eav.title'));
            $grid->column('serialized_workflow',trans('eav::eav.serialized_workflow'));
            $grid->column('process_data',trans('eav::eav.process_data'));
            $grid->column('end_date',trans('eav::eav.end_date'));
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
        return Admin::form(Process::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->select('workflow_id',trans('eav::eav.workflow_id'))->options(Workflow::all()->pluck('name','id'))->rules('required');
            $form->select('entity_id',trans('eav::eav.entity_id'))->options(Entity::all()->pluck('entity_name','id'))->rules('required');
            $form->hidden('user_id',trans('eav::eav.user_id'))->value(Admin::user()->id)->rules('required');
            $form->text('title',trans('eav::eav.title'))->rules('required');
//            $form->text('serialized_workflow',trans('eav::eav.serialized_workflow'))->rules('required');
//            $form->text('process_data',trans('eav::eav.process_data'))->rules('required');
            $form->datetime('end_date',trans('eav::eav.end_date'))->rules('required');
            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));

        });
    }
}
