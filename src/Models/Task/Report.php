<?php
namespace Vreap\Eav\Models\Task;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;

/**
 * Class Tasktype_eav_value
 * @package App\Models
 * @version November 3, 2016, 1:15 pm CST
 */
class Report extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        $this->table = 'report'.Input::get('type');
        parent::__construct($attributes);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(Vreap\Eav\Auth\Database\Administrator::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }

}
