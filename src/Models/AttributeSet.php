<?php

namespace Eav;

use Illuminate\Database\Eloquent\Model;

class AttributeSet extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'attribute_set_name' , 'entity_id'
    ];

//    public function attributes()
//    {
//        return $this->hasManyThrough(Attribute::class, EntityAttribute::class, 'attribute_set_id', 'attribute_id');
//    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id', 'id');
    }
    
    public function attribute_group()
    {
        return $this->hasMany(AttributeGroup::class, 'attribute_set_id');
    }

}
