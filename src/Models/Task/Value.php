<?php
namespace Vreap\Eav\Models\Task;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tasktype_eav_value
 * @package App\Models
 * @version November 3, 2016, 1:15 pm CST
 */
class Value extends Model
{
    use SoftDeletes;
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'task_id',
        'root_id',
        'attribute_id',
        'task_value'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'task_id' => 'integer',
        'root_id' => 'integer',
        'attribute_id' => 'integer',
        'task_value' => 'string'
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
    public function task()
    {
        return $this->belongsTo(\Vreap\Eav\Models\Task\Task::class, 'task_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function attribute()
    {
        return $this->belongsTo(\Vreap\Eav\Models\Task\Attribute::class, 'attribute_id', 'id');
    }

    public function getFieldHtml($customHtml)
    {
        $value = $this->attributes['task_value'];
        $data = json_decode($value);
        if (is_array($data)){
            $html = '';
            foreach ($data as $item) {
                $html .= $customHtml ? str_replace("%value%", $item, $customHtml) : $item;
            }
            return $html;
        } else {
            return $customHtml ? str_replace("%value%", $value, $customHtml) : $value;
        }
    }
}
