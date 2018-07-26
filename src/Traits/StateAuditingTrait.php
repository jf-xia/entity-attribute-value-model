<?php

namespace Fsm\LaravelStateWorkflows;

/**
 * Class StateAuditTrait
 * @package LaravelStateWorkflows
 */
trait StateAuditingTrait
{

    /**
     * Boots the audit trail trait
     */
    public static function bootStateAuditingTrait()
    {
        static::saveInitialState();
    }

    /**
     * Model instance
     *
     * @var
     */
    protected $auditTrailModel;

    /**
     * Class name of the model
     *
     * @var
     */
    protected $auditTrailClass;

    /**
     * Attributes to be saved along with the transition event.
     *
     * @var
     */
    protected $auditTrailAttributes;

    /**
     * Whether of not to save the initial state before any transitions are applied
     *
     * @return boolean
     */
    abstract protected function shouldSaveInitialState() : bool;

    /**
     * Transitions not to keep an audit trail for
     *
     * @return array
     */
    abstract protected function getExcludedTransitions() : array;

    /**
     * If the model's saveInitialState property is set to true,
     * save the initial state to the database when it is first created.
     */
    protected static function saveInitialState()
    {
        static::created(function ($model) {
            $transition = new \Finite\Transition\Transition(null, null, $model->findInitialState());
            if ((bool) $model->shouldSaveInitialState()){
                $model->storeAuditTrail($transition, true);
            }
        });
    }


    /**
     * Initializes the audit trail
     *
     * @param array $options
     */
    protected function initAuditTrail(array $options = [])
    {
        $this->auditTrailAttributes = array_get($options, 'attributes', []);
        $this->auditTrailClass      =  array_get($options, 'auditTrailClass', "\\".get_called_class()."StateTransition");

        if (array_get($options, 'storeAuditTrailOnFirstAfterCallback') === true) {
            // Audit trail State Machine changes at the first 'after' transition
            $this->prependAfter([$this, 'storeAuditTrail']);
        } else {
            // Audit trail State Machine changes at the last 'after' transition
            $this->addAfter([$this, 'storeAuditTrail'], [$this]);
        }
    }


    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $instance = parent::newFromBuilder($attributes, $connection);
        $this->restoreStateMachine($instance);

        return $instance;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model|static $instance
     */
    protected function restoreStateMachine($instance)
    {
        // Initialize the StateMachine when the $instance is loaded from the database and not created via __construct() method
        foreach ($instance->stateMachines as $stateMachine)
        $instance->getStateMachine()->initialize();
    }

    /**
     * Persists the audit trail record to the database
     *
     * @param      \Finite\Event\TransitionEvent|Finite\Transition\Transition $transitionEvent
     * @param bool $save
     */
    public function storeAuditTrail($transitionEvent, $save = true)
    {
        // Save State Machine model to log initial state
        if ($save === true || $this->exists === false) {
            $this->save();
        }
        if (is_a($transitionEvent, "\\Finite\\Event\\TransitionEvent")) {
            $transition = $transitionEvent->getTransition();
        } else {
            $transition = $transitionEvent;
        }
        if (in_array($transition->getName(), $this->dontKeepAuditTrailOfTransitions)) {
            return;
        }

        $this->auditTrailModel = app($this->auditTrailClass);
        if (property_exists($this->auditTrailModel, 'statefulModel')) {
            $this->auditTrailModel->statefulModel = $this;
        }

        $values = [];
        $values['event'] = $transition->getName();
        $initialStates = $transition->getInitialStates();
        if (! empty($initialStates)) {
            $values['from'] = $transitionEvent->getInitialState()->getName();
        }

        $values['to'] = $transition->getState();
        $statefulName = $this->auditTrailModel->statefulName ?: snake_case(str_singular($this->getTable()));
        $values[$statefulName.'_id'] = $this->getKey();//Foreign key

        $statefulType = $statefulName.'_type';
        $columnNames = $this->column_names($this->auditTrailModel->getTable());
        if (in_array($statefulType, $columnNames)) {
            $values[$statefulType] = get_class($this);//For morph relation
        }

        foreach ($this->auditTrailAttributes as $attribute) {
            if (is_array($attribute)) {
                if (is_callable(current($attribute))) {
                    $values[key($attribute)] = call_user_func(current($attribute));
                }
            } else {
                if ($this->getAttribute($attribute)) {
                    $values[$attribute] = $this->getAttribute($attribute);
                }
            }
        }

        $this->auditTrailModel->fill($values);
        $validated = $this->auditTrailModel->save();

        if (! $validated) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Unable to save auditTrail model '".$this->auditTrailClass."'");
        }
    }

    /**
     * @return mixed
     */
    public function getAuditTrailModel()
    {
        return $this->auditTrailModel;
    }

    /**
     * Get the model's transition history
     *
     * @return mixed
     */
    public function transitionHistory()
    {
        return $this->morphMany($this->auditTrailClass, 'stateful');
    }

    /**
     * @param  string $table
     * @param  string $connectionName Database connection name
     *
     * @return array
     */
    protected function column_names($table, $connectionName = null)
    {
        $schema = \DB::connection($connectionName)->getDoctrineSchemaManager();

        return array_map(function ($var) {
            return str_replace('"', '', $var); // PostgreSQL need this replacement
        }, array_keys($schema->listTableColumns($table)));
    }
}