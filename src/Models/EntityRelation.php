<?php

namespace Eav;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class EntityRelation extends Model
{
    protected $fillable = [
        'entity_id', 'relation_entity_id', 'entity_object_id', 'entity_relation_object_id',
//        'entity_attr_id', 'relation_attr_id', 'entity_attr_fk_id', 'relation_attr_fk_id'
    ];

    public $timestamps = false;

    public static $rules = [
        'entity_id' => 'required',
        'relation_entity_id' => 'required',
        'entity_object_id' => 'required',
        'entity_relation_object_id' => 'required',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function relation()
    {
        return $this->belongsTo(Entity::class, 'relation_entity_id','id');
    }

    public function display_attr()
    {
        return $this->belongsTo(Attribute::class, 'display_attr_id','id');
    }

    public function display_attr_code()
    {
        return $this->display_attr->attribute_code;
    }
}
