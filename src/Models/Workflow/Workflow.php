<?php
namespace Eav\Models\Workflow;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tasktype_eav_value
 * @package App\Models
 * @version November 3, 2016, 1:15 pm CST
 */
class Workflow extends Model
{

    protected $table = 'bpmn_workflow';

    public $fillable = [
        'type_id',
        'name',
        'bpmn',
        'version',
        'user_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type_id' => 'integer',
        'name' => 'string',
        'bpmn' => 'string',
        'version' => 'string',
        'user_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

//    public function setBpmnAttribute($value)
//    {
//        $this->attributes['bpmn'] = urldecode($value);
//    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function type()
    {
        return $this->belongsTo(\Eav\Entity::class, 'type_id', 'id');
    }
}
