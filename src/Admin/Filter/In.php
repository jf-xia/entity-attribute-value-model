<?php

namespace Eav\Grid\Filter;

use Encore\Admin\Grid\Filter\AbstractFilter;

class In extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected $query = 'whereIn';

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return mixed
     */
    public function condition($inputs)
    {
        $value = array_get($inputs, $this->column);

        if (is_null($value)) {
            return;
        }

        $this->value = (array) $value;

        return [$this->query => [$this->column, $this->value]];
    }
}
