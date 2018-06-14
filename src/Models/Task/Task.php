<?php

namespace Eav\Models\Task;

use Overtrue\LaravelWeChat\Facade as EasyWeChat;
use Eav\Auth\Database\Administrator;
use Eav\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Database\Eloquent\Relations\BelongsToMany;
//use Illuminate\Http\Request;
//use Illuminate\Support\Str;

/**
 * Class Task
 * @package App\Models
 * @version November 1, 2016, 11:12 am CST
 */
class Task extends Model
{
    use SoftDeletes;

    public $atts;

    protected $dates = ['deleted_at'];

    public $fillable = [
        'title',
        'content',
        'time_limit',
        'price',
        'end_at',
        'root_id',
        'next_id',
        'last_id',
        'user_id',
        'status_id',
        'type_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'title' => 'string',
        'time_limit' => 'double',
        'price' => 'double',
        'end_at' => 'datetime',
        'root_id' => 'integer',
        'next_id' => 'integer',
        'last_id' => 'integer',
        'user_id' => 'integer',
        'status_id' => 'integer',
        'type_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'required',
        'user_id' => 'required',
        'status_id' => 'required',
        'type_id' => 'required',
        'end_at' => 'date_format:"Y-m-d H:i:s"',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function saveComplete($task)
    {
        if ($task->last_id){
            $lastTasks = $task->last;
            $lastTasks->status_id = 5;
            $lastTasks->save();
            return $this->saveComplete($lastTasks);
        }
    }

    public function saveAssign($user_id,$title)
    {
        if ($this->next && $this->next->status_id==5){
            return $this->next;
        }
        \DB::beginTransaction();
        try {
            $baseTitle = $this->root_id ? $this->root->title : $this->title;
            $user = Administrator::find($user_id);
            if ($user){
                $newTask = Task::updateOrCreate(
                    ['id'=>$this->next_id],
                    [
                        "title" => $baseTitle.' ('.Admin::user()->name.$title.')',
                        "user_id" => $user_id,
                        "status_id" => 1,
                        "type_id" => $this->type->next_id,//$input['type']
                        "root_id" => $this->root_id ? $this->root_id : $this->id,
                        "last_id" => $this->id,
                    ]);
                if (!$this->next_id){
                    $message = '通知：'.Admin::user()->name.'提交了一个任务('.$this->type->name.')给您！['.$newTask->title.']<a href="'.env('APP_URL').
                        '/wechat/login?url=/admin/tasks/'.$newTask->id.'/edit" >任务详情</a>';
                    Action::create(["title"=>$message,"activity_id"=>1,"user_id"=>$user->id,
                        "task_id"=>$newTask->root_id,"type_id"=>$newTask->type_id,"is_done"=>1]);
                    $officialAccount = EasyWeChat::officialAccount();
                    $officialAccount->customer_service->message($message)->to($user->wechat_id)->send();
                }
                $this->next_id=$newTask->id;
                $this->save();
                return $newTask;
            }
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error($e);
            return false;
        }
        \DB::commit();
        return false;
    }

//    public function getCurrentAttribute()
//    {
//        return 'dddddd';
//    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(Administrator::class, 'user_id', 'id');
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

    public function value()
    {
        return $this->hasMany(Value::class, 'task_id', 'id');
    }

    public function rootValue()
    {
        return $this->hasMany(Value::class, 'task_id', 'root_id');
    }

    public function allValue()
    {
        return $this->hasMany(Value::class, 'root_id', 'id');
    }

    public function root()
    {
        return $this->belongsTo(static::class, 'root_id', 'id');
    }

    public function child()
    {
        return $this->hasMany(static::class, 'root_id', 'id')->orderBy('id','desc');
    }

    public function current()
    {
        return $this->hasOne(static::class, 'root_id', 'id')->whereNull('next_id');
    }

    public function last()
    {
        return $this->belongsTo(static::class, 'last_id', 'id');
    }

    public function next()
    {
        return $this->belongsTo(static::class, 'next_id', 'id');
    }

//    public function getAttrs()
//    {
//        if (!$this->atts){
//            $this->atts = Attribute::where('type_id','=',$this->attributes['type_id'])->get();
//        }
//        return $this->atts;
//    }

//    public function isEavAttrs($key)
//    {
//        return isset($this->attributes['type_id'])
//                        && $this->attributes['type_id']
//                        && $this->getAttrs()
//                        && !$this->getAttribute($key);
//    }

//    public function __get($key)
//    {
//        if($this->isEavAttrs($key)){
//            $attr = $this->atts ? $this->atts->firstWhere('code','=',$key) : null;
//            $attrArray = $attr ? $attr->toArray() : [];
//            if ($attrArray) {
//                $value = Value::where('task_id','=',$this->attributes['id'])->where('attribute_id','=',$attrArray['id'])->first();
//                $this->attributes[$key] = $value ? $value->task_value : null;
//            }
//        }
//
//        return $this->getAttribute($key);
//    }
//
//    public function __set($key, $value)
//    {
//        if($this->isEavAttrs($key)){
//            $attr = $this->atts ? $this->atts->firstWhere('code','=',$key) : null;
//            $attrArray = $attr ? $attr->toArray() : [];
//            if ($attrArray) {
//                $value = Value::updateOrCreate(['task_id'=>$this->attributes['id'],'attribute_id'=>$attrArray['id']],['task_value'=>$value])->first();
//                $this->attributes[$key] = $value ? $value->task_value : null;
//            }
//        }
//        $this->setAttribute($key, $value);
//    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('user_id', function(Builder $builder) {
//            $user = Admin::user();
//            if(!$user->isAdministrator()){
//                $builder->where('user_id', '=', $user->id);
//            }
//            else if (1){
//                $builder->whereIn('user_id', $user);
//            }
            $builder->orderBy('created_at', 'desc');
        });
    }
}
