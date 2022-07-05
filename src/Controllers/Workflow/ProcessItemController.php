<?php

namespace Eav\Controllers;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Eav\Models\Workflow\ProcessItem;
use Illuminate\Routing\Controller;

class ProcessItemController extends Controller
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
            $content->header(trans('eav::eav.Process Item'));
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
            $content->header(trans('eav::eav.Process Item'));
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
            $content->header(trans('eav::eav.Process Item'));
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
        return Admin::grid(ProcessItem::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->column('process_id',trans('eav::eav.process_id'));
            $grid->column('entity_id',trans('eav::eav.entity_id'));
            $grid->column('user_id',trans('eav::eav.user_id'));
            $grid->column('title',trans('eav::eav.title'));
            $grid->column('from_state',trans('eav::eav.from_state'));
            $grid->column('to_state',trans('eav::eav.to_state'));
            $grid->column('state_id',trans('eav::eav.state_id'));
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
        return Admin::form(ProcessItem::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('process_id',trans('eav::eav.process_id'))->rules('required');
            $form->text('entity_id',trans('eav::eav.entity_id'))->rules('required');
            $form->text('user_id',trans('eav::eav.user_id'))->rules('required');
            $form->text('title',trans('eav::eav.title'))->rules('required');
            $form->text('from_state',trans('eav::eav.from_state'))->rules('required');
            $form->text('to_state',trans('eav::eav.to_state'))->rules('required');
            $form->text('state_id',trans('eav::eav.state_id'))->rules('required');
            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));

        });
    }
}
