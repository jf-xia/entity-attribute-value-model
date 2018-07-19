<?php

namespace Eav\Admin;

use Encore\Admin\Form as AdminForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Form extends AdminForm
{
    /**
     * Get all relations of model from callable.
     *
     * @return array
     */
    public function getRelations()
    {
        $relations = $columns = [];

        foreach ($this->builder->fields() as $field) {
            $columns[] = $field->column();
        }

        foreach (array_flatten($columns) as $column) {
            if (str_contains($column, '.')) {
                list($relation) = explode('.', $column);
                if ((method_exists($this->model, $relation) || is_callable([$this->model, $relation])) &&
                    $this->model->$relation() instanceof Relation
                ) {
                    $relations[] = $relation;
                }
            } elseif ((method_exists($this->model, $column) && !method_exists(Model::class, $column)) ||
                (is_callable([$this->model, $column]) && explode('2',$column.'2')[0]=='hasmany')
            ) {
                $relations[] = $column;
            }
        }

        return array_unique($relations);
    }

    /**
     * Remove files or images in record.
     *
     * @param $id
     */
    protected function deleteFilesAndImages($id)
    {
        $data = $this->model->with($this->getRelations())
            ->findOrFail($id)->toArray();

        $this->builder->fields()->filter(function ($field) {
            return $field instanceof AdminForm\Field\File;
        })->each(function (AdminForm\Field\File $file) use ($data) {
            $file->setOriginal($data);

            $file->destroy();
        });
    }

    /**
     * Set all fields value in form.
     *
     * @param $id
     *
     * @return void
     */
    protected function setFieldValue($id)
    {
        $relations = $this->getRelations();

        $this->model = $this->model->with($relations)->findOrFail($id);

//        static::doNotSnakeAttributes($this->model);

        $data = $this->model->toArray();

        $this->builder->fields()->each(function (AdminForm\Field $field) use ($data) {
            if (!in_array($field->column(), $this->ignored)) {
                $field->fill($data);
            }
        });
    }
}
