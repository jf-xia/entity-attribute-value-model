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

class AttributeSetController extends Controller
{
    use ModelForm;
    public function index()
    {
        $content = Admin::content();
        $content->header(trans('eav::eav.attributes').trans('eav::eav.list'));
        $content->description('...');

        $content->row(function (Row $row) {
            $row->column(6, '');

            $row->column(6, function (Column $column) {
                $form = new \Encore\Admin\Widgets\Form();
                $form->action(admin_base_path('attributeset'));
                $form->text('attribute_group_name', trans('admin.title'))->rules('required');
                $form->text('attribute_set_id', trans('eav::eav.attribute_set_id'))->rules('required');
                $form->hidden('_token')->default(csrf_token());
                $column->append((new Box(trans('admin.new'), $form))->style('success'));

            });
        });
        return $content;
    }

    /**
     * Redirect to edit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        return redirect()->route('attributeset.edit', ['id' => $id]);
    }

    /**
     * Edit interface.
     *
     * @param string $id
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('admin.AttributeSet'));
            $content->description(trans('admin.edit'));

            $content->row($this->form()->edit($id));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        return AttributeGroup::form(function (Form $form) {
            $form->display('attribute_group_id', 'ID');
            $form->text('attribute_group_name', trans('admin.title'))->rules('required');
            $form->text('attribute_set_id', trans('admin.attribute_set_id'))->rules('required');
        });
    }
}
