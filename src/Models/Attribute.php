<?php

namespace Eav;

use Illuminate\Database\Eloquent\Builder;
use ReflectionException;
use Eav\Attribute\Concerns;
use Eav\Attribute\Collection;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use Concerns\QueryBuilder;

    const TYPE_STATIC = 'static';

    public $timestamps = false;
    
    protected $fillable = [
        'entity_id',
        'attribute_code',
        'backend_class',
        'backend_type',
        'backend_table',
        'frontend_class',
        'frontend_type',
        'frontend_label',
        'source_class',
        'default_value',
        'not_list',
        'not_report',
        'is_unique',
        'is_filterable',
        'is_searchable',
        'is_required',
        'order',
        'form_field_html',
        'list_field_html',
        'required_validate_class',
        'help',
        'placeholder',
    ];

    /**
     * Entity instance
     *
     * @var \Eav\Entity
     */
    protected $entity;

    /**
     * Backend instance
     *
     * @var \Eav\Attribute\Backend
     */
    protected $backend;

    /**
     * Frontend instance
     *
     * @var \Eav\Attribute\Frontend
     */
    protected $frontend;

    /**
     * Source instance
     *
     * @var \Eav\Attribute\Source
     */
    protected $source;

    /**
     * Attribute id cache
     *
     * @var array
     */
    protected $attributeIdCache  = [];

    /**
     * Attribute data table name
     *
     * @var string
     */
    protected $dataTable  = null;
    
    /**
     * Set attribute code
     *
     * @param   string $code
     * @return $this
     */
    public function setAttributeCode($code)
    {
        return $this->setAttribute('attribute_code', $code);
    }
    
    /**
     * Set attribute entity instance
     *
     * @param \Eav\Entity $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }
    
    /**
     * Get attribute identifuer
     *
     * @return int | null
     */
    public function getAttributeId()
    {
        return $this->getKey();
    }
    
    /**
     * Get attribute name
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->getAttribute('attribute_code');
    }
    
    /**
     * Get Entity Type Id
     *
     * @return int|string $code
     */
    public function getEntityTypeId()
    {
        return $this->getAttribute('entity_id');
    }
    
    /**
     * Retreive entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return Entity::findById($this->getEntityTypeId());
    }
    
    /**
     * Retreive backend type
     *
     * @return string
     */
    public function getBackendType()
    {
        return $this->getAttribute('backend_type');
    }
    
    /**
     * Retreive frontend type
     *
     * @return string
     */
    public function getFrontendInput()
    {
        return $this->getAttribute('frontend_type');
    }
    
    /**
     * Retreive frontend label
     *
     * @return string
     */
    public function getFrontendLabel()
    {
        return $this->getAttribute('frontend_label');
    }
    
    /**
     * Retreive default value
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->getAttribute('default_value');
    }

    public function options()
    {
        if ($this->usesSource()) {
            return $this->getSource()->toArray();
        }
        return $this->optionValues->toOptions();
    }

    public static function add($data)
    {
        $instance = new static;
                
        try {
            $eavEntity = Entity::where('entity_code', '=', $data['entity_code'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new \Exception("Unable to load Entity : ".$data['entity_code']);
        }
        
        unset($data['entity_code']);
        
        $data['entity_id'] = $eavEntity->id;
        
        $options = [];
        
        if ($data['frontend_type'] == 'select' && empty($data['source_class'])) {
            if (isset($data['options'])) {
                $options = $data['options'];
                unset($data['options']);
            }
        }
        
        
        $instance->fill($data)->save();
        
        if ($instance->getKey()) {
            AttributeOption::add($instance, $options);
        }
    }
        
    public static function remove($data)
    {
        $instance = new static;
                
        try {
            $eavEntity = Entity::where('entity_code', '=', $data['entity_code'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new \Exception("Unable to load Entity : ".$data['entity_code']);
        }
        
        unset($data['entity_code']);
        
        $data['entity_id'] = $eavEntity->id;
        
        $instance->where($data)->delete();
    }

    public function optionValues()
    {
        return $this->hasMany(AttributeOption::class, 'attribute_id');
    }

    public function option()
    {
        return $this->hasMany(AttributeOption::class, 'attribute_id', 'id');
    }

    /**
     * Retrieve entity instance
     *
     * @return \Eav\Entity
     */
    public function getEntity()
    {
        if (!$this->entity) {
            $this->entity = $this->getEntityType();
        }
        return $this->entity;
    }

    /**
     * Check if attribute is static
     *
     * @return bool
     */
    public function isStatic()
    {
        return $this->getAttribute('backend_type') == self::TYPE_STATIC || $this->getAttribute('backend_type') == '';
    }
    
    /**
     * Retrieve backend instance
     *
     * @return \Eav\Attribute\Backend
     */
    public function getBackend()
    {
        if (empty($this->backend)) {
            try {
                if (!$this->getAttribute('backend_class')) {
                    throw new ReflectionException('No class specified');
                }
                $backend = app($this->getAttribute('backend_class'));
            } catch (ReflectionException $e) {
                throw new \Exception('Invalid backend class specified: ' . $this->getAttribute('backend_class'));
            }

            $this->backend = $backend->setAttribute($this);
        }

        return $this->backend;
    }

    /**
     * Retrieve frontend instance
     *
     * @return \Eav\Attribute\Frontend
     */
    public function getFrontend()
    {
        if (empty($this->frontend)) {
            try {
                if (!$this->getAttribute('frontend_class')) {
                    throw new ReflectionException('No class specified');
                }
                $frontend = app($this->getAttribute('frontend_class'));
            } catch (ReflectionException $e) {
                throw new \Exception('Invalid frontend class specified: ' . $this->getAttribute('frontend_class'));
            }
            
            $this->frontend = $frontend->setAttribute($this);
        }

        return $this->frontend;
    }

    /**
     * Retrieve source instance
     *
     * @return \Eav\Attribute\Source
     */
    public function getSource()
    {
        if (empty($this->source)) {
            try {
                if (!$this->getAttribute('source_class')) {
                    throw new ReflectionException('No class specified');
                }
                $source = app($this->getAttribute('source_class'));
            } catch (ReflectionException $e) {
                throw new \Exception('Invalid source class specified: ' . $this->getAttribute('source_class'));
            }
            
            $this->source = $source->setAttribute($this);
        }
        return $this->source;
    }

    public function usesSource()
    {
        return ($this->getAttribute('frontend_type') === 'select' || $this->getAttribute('frontend_type') === 'multiselect')
            && !empty($this->getAttribute('source_class'));
    }
    
    /**
     * Get attribute backend table name
     *
     * @return string
     */
    public function getBackendTable()
    {
        if ($this->dataTable === null) {
            $backendTable = trim($this->getAttribute('backend_table'));
            if (empty($backendTable)) {
                $backendTable  = $this->getEntity()->getEntityTablePrefix().'_'.$this->getAttribute('backend_type');
            }
            $this->dataTable = $backendTable;
        }
        return $this->dataTable;
    }
    
    /*
    protected function getDefaultBackendClass()
    {
        return static::DEFAULT_BACKEND_CLASS;
    }

    protected function getDefaultFrontendClass()
    {
        return static::DEFAULT_FRONTEND_CLASS;
    }
    */
    
    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }
    
    public static function findByCode($code, $entityCode)
    {
        $entity = Entity::findByCode($entityCode);
        
        $instance = new static;
        
        return $instance->newQuery()->where([
            'attribute_code' => $code,
            'entity_id' => $entity->getkey()
        ])->firstOrFail();
    }
    
    
    /**
     * Return attribute id
     *
     * @param string $entityType
     * @param string $code
     * @return int | null
     */
    public function getIdByCode($entityType, $code)
    {
        $k = "{$entityType}|{$code}";
        if (!isset($this->attributeIdCache[$k])) {
            $attribute = \DB::table($this->getTable())
                ->select('id')
                ->where('attribute_code', $code)
                ->where('entity_id', $entityType)
                ->first();
            if ($attribute) {
                $this->attributeIdCache[$k] = $attribute->id;
            } else {
                return null;
            }
        }
        return $this->attributeIdCache[$k];
    }
    
    public function insertAttribute($value, $entityId)
    {
        $insertData = [
            'entity_type_id' => $this->getEntity()->getKey(),
            'attribute_id' => $this->getKey(),
            'entity_id' => $entityId,
            'value' => $value
        ];
        
        return $this->newBaseQueryBuilder()
            ->from($this->getBackendTable())
            ->insert($insertData);
    }

    public function updateAttribute($value, $entityId)
    {
        $attributes = [
            'entity_type_id' => $this->getEntity()->getKey(),
            'attribute_id' => $this->getKey(),
            'entity_id' => $entityId,
        ];

        return $this->newBaseQueryBuilder()
            ->from($this->getBackendTable())
            ->updateOrInsert($attributes, ['value' => $value]);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id', 'id');
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

    public static function backendType()
    {
        return ['','static'=>trans('eav::eav.static'),'int'=>trans('eav::eav.int'),
            'varchar'=>trans('eav::eav.varchar'),'text'=>trans('eav::eav.text'),
            'decimal'=>trans('eav::eav.decimal'),'datetime'=>trans('eav::eav.datetime')];
    }

    public static function frontendType()
    {
        return ['button'=>'button',
            'checkbox'=>'checkbox',
            'color'=>'color',
            'currency'=>'currency',
            'date'=>'date',
            'dateRange'=>'dateRange',
            'datetime'=>'datetime',
            'dateTimeRange'=>'dateTimeRange',
            'datetimeRange'=>'datetimeRange',
            'decimal'=>'decimal',
            'display'=>'display',
            'divider'=>'divider',
            'divide'=>'divide',
            'embeds'=>'embeds',
            'editor'=>'editor',
            'email'=>'email',
            'file'=>'file',
            'hasMany'=>'hasMany',
            'hidden'=>'hidden',
            'id'=>'id',
            'image'=>'image',
            'ip'=>'ip',
            'map'=>'map',
            'mobile'=>'mobile',
            'month'=>'month',
            'multipleSelect'=>'multipleSelect',
            'number'=>'number',
            'password'=>'password',
            'radio'=>'radio',
            'rate'=>'rate',
            'select'=>'select',
            'slider'=>'slider',
            'switch'=>'switch',
            'text'=>'text',
            'textarea'=>'textarea',
            'time'=>'time',
            'timeRange'=>'timeRange',
            'url'=>'url',
            'year'=>'year',
            'html'=>'html',
            'tags'=>'tags',
            'icon'=>'icon',
            'multipleFile'=>'multipleFile',
            'multipleImage'=>'multipleImage',
            'captcha'=>'captcha',
            'listbox'=>'listbox'];
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function(Builder $builder) {
            $builder->orderBy('order');
        });
    }
}
