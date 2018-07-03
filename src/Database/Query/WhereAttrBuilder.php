<?php 
namespace Eav\Database\Query;

trait WhereAttrBuilder
{
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($first);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orWhereColumn($first, $operator = null, $second = null)
    {
        $column = $this->whereAttrColumn($first);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orWhereIn($column, $values)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereNotIn($column, $values, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orWhereNotIn($column, $values)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    protected function whereInExistingQuery($column, $query, $boolean, $not)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereNull($column, $boolean = 'and', $not = false)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orWhereNull($column)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereNotNull($column, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orWhereBetween($column, array $values)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereNotBetween($column, array $values, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orWhereNotBetween($column, array $values)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orWhereNotNull($column)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orWhereDate($column, $operator, $value)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereTime($column, $operator, $value, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orWhereTime($column, $operator, $value)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    public function orHaving($column, $operator = null, $value = null)
    {
        $column = $this->whereAttrColumn($column);
        parent::{__FUNCTION__}(...func_get_args());
    }

    private function whereAttrColumn($column)
    {
        $isStatic = $this->loadAttributes()->firstWhere('attribute_code',$column)->isStatic();
        if (!$this->canUseFlat() && !$isStatic) {
            $column = $column.'_attr.value';
        }
        return $column;
    }
}
