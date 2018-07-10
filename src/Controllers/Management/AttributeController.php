<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use Eav\Attribute;
use Eav\Entity;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

class AttributeController extends Controller
{
    use ModelForm;
    public function index()
    {
        $content = Admin::content();
        $content->header(trans('eav::eav.attributes').trans('eav::eav.list'));
        $content->description('...');

        $grid = Admin::grid(Attribute::class, function (Grid $grid) {
            $grid->column('id','ID')->sortable();
            $grid->column('entity.entity_name',trans('eav::eav.entity'));
            $grid->column('attribute_code',trans('eav::eav.attribute_code'));
            $grid->column('backend_class',trans('eav::eav.backend_class'));
            $grid->column('backend_type',trans('eav::eav.backend_type'));
            $grid->column('backend_table',trans('eav::eav.backend_table'));
            $grid->column('frontend_class',trans('eav::eav.frontend_class'));
            $grid->column('frontend_type',trans('eav::eav.frontend_type'));
            $grid->column('frontend_label',trans('eav::eav.frontend_label'));
            $grid->column('source_class',trans('eav::eav.source_class'));
            $grid->column('default_value',trans('eav::eav.default_value'));
            $grid->column('is_filterable',trans('eav::eav.is_filterable'));
            $grid->column('is_searchable',trans('eav::eav.is_searchable'));
            $grid->column('is_required',trans('eav::eav.is_required'));
            $grid->column('required_validate_class',trans('eav::eav.required_validate_class'));
        });
        $content->body($grid);
        return $content;
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('eav::eav.edit').trans('eav::eav.attributes'));
            $content->description('...');

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
            $content->header(trans('eav::eav.create').trans('eav::eav.attributes'));
            $content->description('...');
            $content->body($this->form());
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Attribute::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->select('entity_id',trans('eav::eav.entity'))->options(Entity::all()->pluck('entity_name','entity_id'))->rules('required');
            $this->formFileds($form);
            $form->subForm('option',trans('eav::eav.option'), function (Form\NestedForm $form) {
//                $form->display('option_id', '');
                $form->text('label',trans('eav::eav.label'))->setElementClass('option_label');
                $form->text('value',trans('eav::eav.value'));
            });
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
}
