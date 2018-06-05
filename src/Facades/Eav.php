<?php

namespace Vreap\Eav\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Admin.
 *
 * @method static \Vreap\Eav\Grid grid($model, \Closure $callable)
 * @method static \Vreap\Eav\Form form($model, \Closure $callable)
 * @method static \Vreap\Eav\Tree tree($model, \Closure $callable = null)
 * @method static \Vreap\Eav\Layout\Content content(\Closure $callable = null)
 * @method static \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void css($css = null)
 * @method static \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void js($js = null)
 * @method static \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void script($script = '')
 * @method static \Illuminate\Contracts\Auth\Authenticatable|null user()
 * @method static string title()
 * @method static void navbar(\Closure $builder = null)
 * @method static void registerAuthRoutes()
 * @method static void extend($name, $class)
 */
class Eav extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vreap\Eav\Eav::class;
    }
}
