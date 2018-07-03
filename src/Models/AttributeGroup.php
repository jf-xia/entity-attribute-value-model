<?php

namespace Eav;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class AttributeGroup extends Model
{

    protected $primaryKey = 'attribute_group_id';
    
    public $timestamps = false;
    
    protected $fillable = [
        'attribute_set_id', 'attribute_group_name', 'order',
    ];

    public function attributes()
    {
        return $this->hasManyThrough(Attribute::class, EntityAttribute::class, 'attribute_group_id', 'attribute_id');
    }

    public function attribute_set()
    {
        return $this->belongsTo(AttributeSet::class, 'attribute_set_id', 'attribute_set_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function(Builder $builder) {
            $builder->orderBy('order');
        });
    }
}
