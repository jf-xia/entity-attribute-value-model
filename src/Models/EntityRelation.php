<?php

namespace Eav;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class EntityRelation extends Model
{
    protected $primaryKey = 'relation_id';

    protected $fillable = [
        'entity_id', 'relation_type', 'relation_entity_id',
        'entity_attr_id', 'relation_attr_id', 'entity_attr_fk_id', 'relation_attr_fk_id'
    ];





    public $timestamps = false;

    public static $rules = [
        'entity_id' => 'required',
        'relation_type' => 'required',
        'relation_entity_id' => 'required',
        'entity_attr_id' => 'required',
        'relation_attr_id' => 'required',
        'entity_attr_fk_id' => 'required',
        'relation_attr_fk_id' => 'required',
    ];

    public static function relationTypeOption()
    {
        return [//hasManyThrough
            'hasOne' => 'hasOne',
            'hasMany' => 'hasMany',
            'belongsTo' => 'belongsTo',
            'belongsToMany' => 'belongsToMany',
        ];
    }
        
    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id', 'relation_id');
    }
}
