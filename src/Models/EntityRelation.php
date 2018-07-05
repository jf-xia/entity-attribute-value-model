<?php

namespace Eav;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class EntityRelation extends Model
{
    protected $primaryKey = 'entity_relation_id';

    protected $fillable = [
        'entity_id', 'relation_type', 'relation_entity_id',
//        'entity_attr_id', 'relation_attr_id', 'entity_attr_fk_id', 'relation_attr_fk_id'
    ];

    public $timestamps = false;

    public static $rules = [
        'entity_id' => 'required',
        'relation_type' => 'required',
        'relation_entity_id' => 'required',
    ];

    public static function relationTypeOption()
    {
        return [//hasManyThrough
            'hasOne' => 'hasOne',
            'hasMany' => 'hasMany',
//            'belongsTo' => 'belongsTo',
//            'belongsToMany' => 'belongsToMany',
        ];
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function relation()
    {
        return $this->belongsTo(Entity::class, 'relation_entity_id','entity_id');
    }

    public function relation2Entitys()
    {
        return $this->hasMany(EntityRelationId::class,'entity_relation_id','entity_relation_id');
    }

    public function getRelation2Entitys()
    {
        $entityObject = $this->relation->entity_class;
        return $entityObject::whereIn('id',$this->relation2Entitys->pluck('entity_relation_object_id'))->get();
    }

    public function relation2Entity()
    {
        //todo relation2Entity base hasManyThrough
        return $this->hasManyThrough($this->relation->entity_class, EntityRelationId::class, 'entity_relation_id', 'id',null,'entity_relation_object_id');
    }
}
