<?php

namespace Vreap\Eav\Models\Task;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tasktype_eav
 * @package App\Models
 * @version November 3, 2016, 1:12 pm CST
 */
class Attribute extends Model
{
    use SoftDeletes;


    protected $dates = ['deleted_at'];


    public $fillable = [
        'type_id',
        'code',
        'frontend_label',
        'frontend_input',
        'frontend_size',
        'rules',
        'not_list',
        'not_report',
        'is_required',
        'is_unique',
        'is_filter',
        'form_field_html',
        'list_field_html',
        'option',
        'user_id',
        'orderby',
        'note'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type_id' => 'integer',
        'code' => 'string',
        'frontend_label' => 'string',
        'frontend_input' => 'string',
        'frontend_size' => 'integer',
        'rules' => 'string',
        'not_list' => 'integer',
        'not_report' => 'integer',
        'is_required' => 'integer',
        'is_unique' => 'integer',
        'is_filter' => 'integer',
        'form_field_html' => 'string',
        'list_field_html' => 'string',
        'option' => 'string',
        'orderby' => 'integer',
        'permission_id' => 'integer',
        'note' => 'string'
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
    public function type()
    {
        return $this->belongsTo(\Vreap\Eav\Models\Task\Type::class, 'type_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\Vreap\Eav\Auth\Database\Administrator::class, 'user_id', 'id');
    }

    public function value()
    {
        return $this->hasMany(\Vreap\Eav\Models\Task\Value::class, 'attribute_id', 'id');
    }

    public function getListHtml($value)
    {//todo 待优化
        $customHtml = $this->attributes['list_field_html'];
        $data = json_decode($value);
        if (is_array($data)){
            $html = '';
            foreach ($data as $item) {
                $html .= $customHtml ? str_replace("%value%", $item, $customHtml) : $item;
            }
            return $html;
        } elseif (!$value){
            return '';
        } else {
            return $customHtml ? str_replace("%value%", $value, $customHtml) : $value;
        }
    }
}
