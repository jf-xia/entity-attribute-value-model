<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use Eav\Attribute;
use Eav\AttributeSet;
use Eav\Entity;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

class EntityController extends Controller
{

    public function index()
    {
        $content = Admin::content();
        $content->header(trans('eav::eav.entity'));
        $content->description('...');
        $entity = Entity::first();
        //$entity->describe()->pluck('DATA_TYPE','COLUMN_NAME')
//        dd($entity->attributeSet->toArray());
        $content->body(Admin::grid(Entity::class, function (Grid $grid) {
            $grid->column('entity_id', 'ID')->sortable();
            $grid->column('entity_code',trans('eav::eav.entity_code'));
            $grid->column('entity_class',trans('eav::eav.entity_class'));
            $grid->column('entity_table',trans('eav::eav.entity_table'));
            $grid->column('defaultAttributeSet.attribute_set_name',trans('eav::eav.default_attribute_set_id'));
//            $grid->column('attributeSet',trans('eav::eav.additional_attribute_table'))
//                ->pluck('attribute_set_name')->label();
            $grid->column('is_flat_enabled',trans('eav::eav.is_flat_enabled'))
                ->display(function($val){return status()[$val];});
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
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('eav::eav.entity'));
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
            $content->header(trans('task.Create'));
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
        return Admin::form(Entity::class, function (Form $form) {
            $form->display('entity_id', 'ID');
            $form->text('entity_code',trans('eav::eav.entity_code'));
            $form->text('entity_class',trans('eav::eav.entity_class'));
            $form->text('entity_table',trans('eav::eav.entity_table'));
            $form->select('default_attribute_set_id',trans('eav::eav.default_attribute_set_id'))
                ->options(AttributeSet::all()->pluck('attribute_set_name','attribute_set_id'));
//            $form->column('additional_attribute_table',trans('eav::eav.additional_attribute_table'));
            $form->select('is_flat_enabled',trans('eav::eav.is_flat_enabled'))->options(status());
            $form->hasMany('attributes', function (Form\NestedForm $form) {
                $form->display('attribute_id', 'ID');
                $form->text('attribute_code',trans('eav::eav.attribute_code'));
                $form->text('backend_class',trans('eav::eav.backend_class'));
                $form->text('backend_type',trans('eav::eav.backend_type'));
                $form->text('backend_table',trans('eav::eav.backend_table'));
                $form->text('frontend_class',trans('eav::eav.frontend_class'));
                $form->text('frontend_type',trans('eav::eav.frontend_type'));
                $form->text('frontend_label',trans('eav::eav.frontend_label'));
                $form->text('source_class',trans('eav::eav.source_class'));
                $form->text('default_value',trans('eav::eav.default_value'));
                $form->text('is_filterable',trans('eav::eav.is_filterable'));
                $form->text('is_searchable',trans('eav::eav.is_searchable'));
                $form->text('is_required',trans('eav::eav.is_required'));
                $form->text('required_validate_class',trans('eav::eav.required_validate_class'));
            });
        });
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->edit($id);
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
        return $this->form()->update($id);
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
        if ($this->form()->destroy($id)) {
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
        return $this->form()->store();
    }
}