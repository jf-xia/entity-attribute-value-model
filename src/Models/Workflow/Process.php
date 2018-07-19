<?php
namespace Eav\Models\Workflow;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tasktype_eav_value
 * @package App\Models
 * @version November 3, 2016, 1:15 pm CST
 */
class Process extends Model
{

    protected $table = 'bpmn_process';

    public $fillable = [
        'workflow_id',
        'entity_id',
        'user_id',
        'title',
        'serialized_workflow',
        'process_data',
        'end_date',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'workflow_id' => 'integer',
        'entity_id' => 'integer',
        'user_id' => 'integer',
        'title' => 'string',
        'serialized_workflow' => 'string',
        'process_data' => 'string',
        'end_date' => 'datetime',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'user_id', 'id');
    }

    public function task()
    {
        return $this->belongsTo(\Eav\Models\Task\Task::class, 'entity_id', 'id');
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id', 'id');
    }
}
