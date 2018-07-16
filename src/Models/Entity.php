<?php

namespace Eav;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected static $baseEntity = [];
    protected static $entityIdCache = [];
    
    protected $fillable = [
        'entity_code', 'entity_name', 'entity_class', 'entity_table',
        'default_attribute_set_id', 'additional_attribute_table',
        'relation_entity_ids', 'is_flat_enabled', 'entity_desc'
    ];
    
    public $timestamps = false;

    public static $rules = [
        'entity_code' => 'required|unique:entities',
        'entity_name' => 'required|unique:entities',
        'entity_class' => 'required|unique:entities',
        'entity_table' => 'required|unique:entities',
    ];
    
    public function canUseFlat()
    {
        return $this->getAttribute('is_flat_enabled');
    }
    
    public function getEntityTablePrefix()
    {
        $tableName = Str::singular($this->getAttribute('entity_table'));
        $tablePrefix = $this->getConnection()->getTablePrefix();
        if ($tablePrefix != '') {
            $tableName = "$tablePrefix.$tableName";
        }
        return $tableName;
    }

    public function setRelationEntityIdsAttribute($value)
    {
        if (is_array($value)){
            $this->attributes['relation_entity_ids'] = json_encode($value);
        }
    }

    public function getRelationEntityIdsAttribute()
    {
        return ($ids = $this->attributes['relation_entity_ids']) ? json_decode($ids) : $ids;
    }

    public function attributeSet()
    {
        if ($this->default_attribute_set_id) {
            return $this->defaultAttributeSet();
        }
        return $this->hasOne(AttributeSet::class, 'entity_id');
    }

    public function getDefaultAttributeSetIdAttribute()
    {
        if (Input::get('set')) {
            $this->attributes['default_attribute_set_id'] = Input::get('set');
        }
        return $this->attributes['default_attribute_set_id'] ?? '';
    }

    public function defaultAttributeSet()
    {
        return $this->belongsTo(AttributeSet::class, 'default_attribute_set_id');
    }

    public function attributeSets()
    {
        return $this->hasMany(AttributeSet::class, 'entity_id');
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class,'entity_id');
    }

//    public function attributes_form()//todo 4 debug for hasManyThrough in form
//    {
//        return $this->hasManyThrough(Attribute::class, EntityAttribute::class, 'entity_id', 'id');
//    }

//    public function object_relation()
//    {
//        return $this->hasManyThrough(static::class, EntityRelation::class,'aaaaentity_id','bbbbbbid','ccccid','dddddrelation_entity_id');//
//    }

    public function entity_relations()
    {
        return $this->hasMany(EntityRelation::class, 'entity_id')
            ->whereIn('relation_entity_id',$this->getRelationEntityIdsAttribute())
            ->with(['entity','relation']);
    }
    public static function findByCode($code)
    {
        if (!isset(static::$entityIdCache[$code])) {
            $entity= static::where('entity_code', '=', $code)->firstOrFail();
                                            
            static::$entityIdCache[$entity->getAttribute('entity_code')] = $entity->getKey();
            
            static::$baseEntity[$entity->getKey()] = $entity;
        }
                    
        return static::$baseEntity[static::$entityIdCache[$code]];
    }
    
    public static function findById($id)
    {
        if (!isset(static::$baseEntity[$id])) {
            $entity = static::findOrFail($id);
            
            static::$entityIdCache[$entity->getAttribute('entity_code')] = $entity->getKey();
            
            static::$baseEntity[$id] = $entity;
        }
                    
        return static::$baseEntity[$id];
    }
    
    public function describe()
    {
        $table = $this->getAttribute('entity_table');
        
        $connection = \DB::connection();
        
        $database = $connection->getDatabaseName();

        $table = $connection->getTablePrefix().$table;
        
        $result = \DB::table('information_schema.columns')
                ->where('table_schema', $database)
                ->where('table_name', $table)
                ->get();
                
        return new Collection(json_decode(json_encode($result), true));
    }

    /**
     * create entity with rbac data
     *
     * @param $form
     */
    public function savedRolePermissionMenu($model)
    {
        $roleLeader = Role::updateOrCreate(
            ['name' => $model->getOriginal('entity_name').'Leader', 'slug' => $model->getOriginal('entity_code').'_leader'],
            ['name' => $model->entity_name.'Leader', 'slug' => $model->entity_code.'_leader']);
        $roleBase = Role::updateOrCreate(
            ['name' => $model->getOriginal('entity_name').'Base', 'slug' => $model->getOriginal('entity_code').'_base'],
            ['name' => $model->entity_name.'Base', 'slug' => $model->entity_code.'_base']);
        $roleRelation = Role::updateOrCreate(
            ['name' => $model->getOriginal('entity_name').'Relation', 'slug' => $model->getOriginal('entity_code').'_relation'],
            ['name' => $model->entity_name.'Relation', 'slug' => $model->entity_code.'_relation']);
        $en = $model->entity_name;
        $ec = $model->entity_code;
        $permsList=Permission::updateOrCreate(
            ['name'=>trans('eav::eav.list').$model->getOriginal('entity_name'),'slug'=>'list_'.$model->getOriginal('entity_code')],
            ['name'=>trans('eav::eav.list').$en,'slug'=>'list_'.$ec,'http_method'=>['GET'],'http_path'=>"/".$ec]);
        $permsView=Permission::updateOrCreate(
            ['name'=>trans('eav::eav.view').$model->getOriginal('entity_name'),'slug'=>'view_'.$model->getOriginal('entity_code')],
            ['name'=>trans('eav::eav.view').$en,'slug'=>'view_'.$ec,'http_method'=>['GET'],'http_path'=>"/".$ec."/*"]);
        $permsCreate=Permission::updateOrCreate(
            ['name'=>trans('eav::eav.create').$model->getOriginal('entity_name'),'slug'=>'create_'.$model->getOriginal('entity_code')],
            ['name'=>trans('eav::eav.create').$en,'slug'=>'create_'.$ec,'http_method'=>['POST'],'http_path'=>"/".$ec."/*"]);
        $permsEdit=Permission::updateOrCreate(
            ['name'=>trans('eav::eav.edit').$model->getOriginal('entity_name'),'slug'=>'update_'.$model->getOriginal('entity_code')],
            ['name'=>trans('eav::eav.edit').$en,'slug'=>'update_'.$ec,'http_method'=>['PUT','PATCH'],'http_path'=>"/".$ec."/*"]);
        $permsDelete=Permission::updateOrCreate(
            ['name'=>trans('eav::eav.delete').$model->getOriginal('entity_name'),'slug'=>'delete_'.$model->getOriginal('entity_code')],
            ['name'=>trans('eav::eav.delete').$en,'slug'=>'delete_'.$ec,'http_method'=>['DELETE'],'http_path'=>"/".$ec."/*"]);
        $permsExport=Permission::updateOrCreate(
            ['name'=>trans('eav::eav.export').$model->getOriginal('entity_name'),'slug'=>'export_'.$model->getOriginal('entity_code')],
            ['name'=>trans('eav::eav.export').$en,'slug'=>'export_'.$ec]);
        $menu = Menu::updateOrCreate(
            ['title'=>$model->getOriginal('entity_name')],
            ['parent_id'=>1,'order'=>$model->id,'title'=>$en,'icon'=>'fa-puzzle-piece','uri'=>'/'.$ec]);
        if (!$model->exists()) {
            Administrator::first()->roles()->save($roleLeader);
            $roleLeader->permissions()->saveMany([$permsList,$permsView,$permsCreate,$permsEdit,$permsDelete,$permsExport]);
            $roleBase->permissions()->saveMany([$permsList,$permsView,$permsEdit]);
            $roleRelation->permissions()->saveMany([$permsList,$permsView]);
            $menu->roles()->saveMany([$roleLeader,$roleBase,$roleRelation]);
        }
    }

    /**
     * create entity with Attributes & set & group data
     *
     * @param $form
     */
    public function createdAttrSetGroup4Entity($model)
    {
        $attributeSet = AttributeSet::create(['entity_id'=>$model->id,'attribute_set_name'=>'基本']);
        $model->attribute_set_id = $attributeSet->id;
        $model->save();
        $attributeGroup = AttributeGroup::create(
            ['attribute_set_id'=>$attributeSet->id,'attribute_group_name'=>'基本','order'=>0]);
        Attribute::insert([
            ['entity_id'=>$attributeSet->entity_id, 'attribute_code'=>'created_at', 'backend_type'=>'static',
                'frontend_type'=>'datetime', 'frontend_label'=>trans('eav::eav.created_at'), 'is_filterable'=>1,'order'=>999],
            ['entity_id'=>$attributeSet->entity_id, 'attribute_code'=>'updated_at', 'backend_type'=>'static',
                'frontend_type'=>'datetime', 'frontend_label'=>trans('eav::eav.updated_at'), 'is_filterable'=>1,'order'=>999]
        ]);
        $insertEAs = [];
        foreach (Attribute::where('entity_id', $attributeSet->entity_id)->get() as $attr) {
            $insertEAs[] = ['entity_id'=>$attributeSet->entity_id, 'attribute_set_id'=>$attributeSet->id,
                'attribute_group_id'=>$attributeGroup->id, 'attribute_id'=>$attr->id,];
        }
        EntityAttribute::insert($insertEAs);
    }

    public function creatingModelFile($model)
    {
        if (!class_exists($model->entity_class)){
            \Artisan::call('eav:make:entity',[
                'name'=>$model->entity_code,
                'class'=>$model->entity_class,
//                        '--path'=>'app/Models/Eav', //todo 3 Models path change
            ]);
            \Artisan::call('migrate');
            return true;
        }
        return false;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function($model) {
            $model->entity_class = $model->entity_class ? : '\\App\\'.ucfirst($model->entity_code);
            $model->entity_table = str_plural($model->entity_code);
        });
        static::creating(function($model) {
            $model->creatingModelFile($model);
        });
        static::created(function($model) {
            $model->createdAttrSetGroup4Entity($model);
            $model->savedRolePermissionMenu($model);
        });
        static::updating(function($model) {
            //todo 4 updatingModelFile if entity_code & entity_class updating
        });
        static::saved(function($model) {
//            $model->savedRolePermissionMenu($model);
        });
        static::deleting(function($model) {
            //todo 4 Backup Files and deleting Files
        });
        static::deleted(function($model) {
            //todo 4 Backup Data and deleted relation Data
        });
    }
}
