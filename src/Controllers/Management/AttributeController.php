<?php

namespace Eav\Controllers;

use App\Http\Controllers\Controller;
use Eav\Attribute;
use Eav\Entity;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
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
            $grid->id('ID')->sortable();
            foreach ($this->attrs() as $attr) {
                $grid->column($attr->attribute_code,$attr->frontend_label);
            }
        });
        $content->body($grid);
        return $content;
    }
}
