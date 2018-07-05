<?php

namespace Eav;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class EntityRelationId extends Model
{
    protected $primaryKey = 'entity_relation_ids_id';

    protected $fillable = [
        'entity_relation_id', 'entity_object_id', 'entity_relation_object_id',
    ];

    public $timestamps = false;

    public static $rules = [
        'entity_relation_id' => 'required',
        'entity_object_id' => 'required',
        'entity_relation_object_id' => 'required',
    ];

    public function entity_relation()
    {
        return $this->belongsTo(EntityRelation::class, 'entity_relation_id');
    }

//    public function entity_object()
//    {
//        todo debug $this->entity_relation->relation->entity_class error print out "product"
//        dd($this->entity_relation->with('relation')->first()->relation->entity_class);
//        return $this->belongsTo($this->entity->entity_class, 'entity_relation_object_id','id');
//    }
}
