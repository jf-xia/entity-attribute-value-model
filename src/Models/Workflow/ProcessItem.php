<?php
namespace Eav\Models\Workflow;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tasktype_eav_value
 * @package App\Models
 * @version November 3, 2016, 1:15 pm CST
 */
class ProcessItem extends Model
{

    protected $table = 'bpmn_process_item';

    public $fillable = [
        'process_id',
        'user_id',
        'entity_id',
        'title',
        'from_state',
        'to_state',
        'state_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'process_id' => 'integer',
        'user_id' => 'integer',
        'entity_id' => 'integer',
        'title' => 'string',
        'from_state' => 'string',
        'to_state' => 'string',
        'state_id' => 'string',
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
        return $this->belongsTo(Process::class, 'process_id', 'id');
    }
}
