<?php

namespace Eav\Grid\Filter;

use Encore\Admin\Grid\Filter\AbstractFilter;

class Equal extends AbstractFilter
{
    public function condition($inputs)
    {
        $value = array_get($inputs, $this->column);

        if (!isset($value)) {
            return;
        }

        $this->value = $value;

        return [$this->query => [$this->column, $this->value]];
    }
}
