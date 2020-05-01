<?php

namespace Baka\Blameable;

use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\DI;
use Throwable;

class Blameable extends Behavior implements BehaviorInterface
{
    /**
     * Fields that want to be excluded from the audit.
     *
     * @var array
     */
    protected $excludeFields = [];

    /**
     * @var array
     */
    protected $snapshot;

    /**
     * @var array
     */
    protected $changedFields;

    /**
     * custom fields from the current model.
     *
     * @var array
     */
    protected $customFields = [];

    /**
     * Can update custom fields.
     *
     * @var boolean
     */
    protected $canUpdateCustomField = false;

    public const DELETE = 'D';
    public const UPDATE = 'U';
    public const CREATE = 'C';

    /**
     * {@inheritdoc}
     *
     * @param string eventType
     * @param \Phalcon\Mvc\ModelInterface $model
     */
    public function notify($eventType, ModelInterface $model)
    {
        // `auditExcludeFields` allows default fields to be ignored through all models.
        if (property_exists($model, 'auditExcludeFields') && is_array($model->auditExcludeFields)) {
            $this->excludeFields = $model->auditExcludeFields;
        }

        // `additionalAuditExcludeFields` allows additional fields to be excluded from particular models.
        if (property_exists($model, 'additionalAuditExcludeFields') && is_array($model->auditExcludeFields)) {
            $this->excludeFields = array_merge($this->excludeFields, $model->additionalAuditExcludeFields);
        }

        //Fires 'logAfterUpdate' if the event is 'afterCreate'
        if ($eventType == 'afterCreate') {
            return $this->auditAfterCreate($model);
        }

        //given that custom field are dynamic fields we need to capture their before state , before proceeding with the audit
        if ($eventType == 'beforeUpdate') {
            if (!empty($model->customFields)) {
                $this->canUpdateCustomField = true;
                $this->customFields = $model->getAllCustomFields();
            }
        }

        //Fires 'logAfterUpdate' if the event is 'afterUpdate'
        if ($eventType == 'afterUpdate') {
            return $this->auditAfterUpdate($model);
        }

        // //Fires 'logBeforeDelete' if the event is 'afterUpdate'
        if ($eventType == 'beforeDelete') {
            return $this->auditBeforeDelete($model);
        }

        // Fires 'collectData' if the event is 'beforeUpdate'
        if ($eventType == 'beforeUpdate') {
            return $this->collectData($model);
        }
    }

    /**
     * Creates an Audit isntance based on the current enviroment.
     *
     * @param  string                      $type
     * @param  \Phalcon\Mvc\ModelInterface $model
     * @return Audit
     */
    public function createAudit($type, ModelInterface $model): Audits
    {
        // Grab user data from the registered service
        if (!method_exists($model, 'getBlameableUser')) {
            $user = $model->getDI()->has('userData') ? $model->getDI()->get('userData') : null;
        } else {
            $user = $model->getBlameableUser();
        }

        // Get the request service
        $request = $model->getDI()->has('request') ? $model->getDI()->get('request') : null;

        $audit = new Audits();

        $anonymous = 0;

        //Get the user_id from di service
        $audit->users_id = is_object($user) ? $user->getId() : $anonymous;

        //The model who performed the action
        $audit->entity_id = $model->id;

        //The model who performed the action
        $audit->model_name = get_class($model);

        //The client IP address
        $audit->ip = is_object($request) ? $request->getClientAddress() : '127.0.0.1';

        //Action is an update
        $audit->type = $type;

        //Current time
        $audit->created_at = date('Y-m-d H:i:s');

        return $audit;
    }

    /**
     * Audits an CREATE operation.
     *
     * @param  \Phalcon\Mvc\ModelInterface $model
     * @return boolean
     */
    public function auditAfterCreate(ModelInterface $model)
    {
        $audit = $this->createAudit(self::CREATE, $model);
        $fields = $model->getModelsMetaData()->getAttributes($model);
        $hasOne = $model->getDI()->getModelsManager()->getHasOne($model);
        $belongsTo = $model->getDI()->getModelsManager()->getBelongsTo($model);
        $relations = array_merge($hasOne, $belongsTo);
        $details = [];

        foreach ($fields as $field) {
            $undefiendPropertyBreakLoop = false;
            if (in_array($field, $this->excludeFields)) {
                continue;
            }

            $newValue = $newValueText = $model->readAttribute($field);
            if (is_null($newValue) || empty($newValue)) {
                continue;
            }

            $relatedData = $this->getRelationData($model, $relations, $field);
            if (!empty($relatedData)) {
                // These means it is a multi level relationship, recursive!
                if (is_array($relatedData['title'])) {
                    $auditModel = $model;
                    $auditData = $relatedData['title'];

                    while (is_array(current($auditData))) {
                        if (empty($auditModel->{key($auditData)})) {
                            $undefiendPropertyBreakLoop = true;
                            break;
                        }

                        $auditModel = $auditModel->{key($auditData)};
                        $auditData = $auditData[key($auditData)];
                    }

                    if ($undefiendPropertyBreakLoop) {
                        break;
                    }

                    foreach ($auditData as &$data) {
                        $data = $auditModel->{$data};
                    }

                    $newValueText = implode(' ', $auditData);
                } else {
                    $newValueText = is_object($model->{$relatedData['alias']}) ? $model->{$relatedData['alias']}->{$relatedData['title']} : null;
                }
            }

            $auditDetail = new AuditsDetails();
            $auditDetail->field_name = $field;
            $auditDetail->old_value = null;
            $auditDetail->old_value_text = null;
            $auditDetail->new_value = $newValue;
            $auditDetail->new_value_text = $newValueText;

            $details[] = $auditDetail;
        }

        // Add custom fields to the fields
        if (!empty($model->customFields)) {
            foreach ($model->customFields as $field => $value) {
                if (empty($value)) {
                    continue;
                }

                $auditDetail = new AuditsDetails();
                $auditDetail->field_name = $field;
                $auditDetail->old_value = null;
                $auditDetail->old_value_text = null;
                $auditDetail->new_value = $value;
                $auditDetail->new_value_text = $value;

                $details[] = $auditDetail;
            }
        }

        $audit->details = $details;

        //Create a new audit
        if (!$audit->save()) {
            $this->log(current($audit->getMessages()));
            return null;
        }

        return $audit;
    }

    /**
     * Audits an UPDATE operation.
     *
     * @param  \Phalcon\Mvc\ModelInterface $model
     *
     * @return boolean
     */
    public function auditAfterUpdate(ModelInterface $model)
    {
        $changedFields = $this->changedFields;
        $originalData = $this->snapshot;
        $hasOne = $model->getDI()->getModelsManager()->getHasOne($model);
        $belongsTo = $model->getDI()->getModelsManager()->getBelongsTo($model);
        $relations = array_merge($hasOne, $belongsTo);
        $details = [];

        foreach ($changedFields as $field) {
            if (in_array($field, $this->excludeFields) || !array_key_exists($field, $originalData)) {
                continue;
            }

            $newValue = $newValueText = $model->readAttribute($field);
            if (is_null($newValue)) {
                continue;
            }

            $relatedData = $this->getRelationData($model, $relations, $field);
            $oldValueText = '-';

            if (!empty($relatedData)) {
                // These means it is a multi level relationship, recursive!
                if (is_array($relatedData['title'])) {
                    $auditModel = $model;
                    $auditData = $relatedData['title'];

                    while (is_array(current($auditData))) {
                        $auditModel = $auditModel->{key($auditData)};
                        $auditData = $auditData[key($auditData)];
                    }

                    foreach ($auditData as &$data) {
                        $data = $auditModel ? $auditModel->{$data} : null;
                    }

                    $newValueText = implode(' ', $auditData);
                } else {
                    $newValueText = is_object($model->{$relatedData['alias']}) ? $model->{$relatedData['alias']}->{$relatedData['title']} : null;
                }

                // These means it is a multi level relationship, recursive!
                if (is_array($relatedData['title'])) {
                    $auditModel = $model;
                    $auditData = $relatedData['title'];
                    $undefiendPropertyBreakLoop = false;

                    while (is_array(current($auditData))) {
                        if (empty($auditModel->{key($auditData)})) {
                            $undefiendPropertyBreakLoop = true;
                            break;
                        }
                        $auditModel = $auditModel->{key($auditData)};
                        $auditData = $auditData[key($auditData)];
                    }

                    $auditModel = $auditModel->findFirst($originalData[$field]);

                    if ($undefiendPropertyBreakLoop) {
                        break;
                    }

                    foreach ($auditData as &$data) {
                        $data = $auditModel->{$data};
                    }

                    $oldValueText = implode(' ', $auditData);
                } else {
                    // Seems like a clean workaround. Will have to keep a tab to check in the future
                    if ($model->{'get' . ucfirst($relatedData['alias'])}()) {
                        $oldValueText = $model->{'get' . ucfirst($relatedData['alias'])}()
                            ->findFirst($originalData[$field])
                            ->{$relatedData['title']};
                    }
                }
            }

            if (!is_null($newValue) && $newValueText != $originalData[$field] && $field != 'updated_at') {
                $auditDetail = new AuditsDetails();
                $auditDetail->field_name = $field;
                $auditDetail->old_value = $originalData[$field];
                $auditDetail->old_value_text = $oldValueText;
                $auditDetail->new_value = $newValue;
                $auditDetail->new_value_text = !is_null($newValueText) ? $newValueText : ' ';
                $details[] = $auditDetail;
            }
        }

        // Add custom fields to the fields
        if ($this->canUpdateCustomField) {
            $oldCustomFields = $this->customFields;
            foreach ($model->customFields as $field => $value) {
                if ((array_key_exists($field, $oldCustomFields)
                        && $oldCustomFields[$field] != $value && !empty($value))
                    ) {
                    $auditDetail = new AuditsDetails();
                    $auditDetail->field_name = $field;
                    $auditDetail->old_value = $oldCustomFields[$field] ?? '';
                    $auditDetail->old_value_text = $oldCustomFields[$field] ?? '';
                    $auditDetail->new_value = $value;
                    $auditDetail->new_value_text = $value;

                    $details[] = $auditDetail;
                }
            }
        }

        //Create a new audit
        if (!empty($details)) {
            $audit = $this->createAudit(self::UPDATE, $model);
            $audit->details = $details;
            if (!$audit->save()) {
                $this->log(current($audit->getMessages()));
            }

            return true;
        }

        return false;
    }

    /**
     * Creates an Audit isntance based on the current enviroment.
     *
     * @param  string                      $type
     * @param  \Phalcon\Mvc\ModelInterface $model
     * @return Audit
     */
    public function auditBeforeDelete(ModelInterface $model)
    {
        $fields = $model->getModelsMetaData()->getAttributes($model);
        $hasOne = $model->getDI()->getModelsManager()->getHasOne($model);
        $belongsTo = $model->getDI()->getModelsManager()->getBelongsTo($model);
        $relations = array_merge($hasOne, $belongsTo);
        $originalData = $this->snapshot;
        $details = [];

        foreach ($fields as $field) {
            if (in_array($field, $this->excludeFields)) {
                continue;
            }

            $newValue = $newValueText = $model->readAttribute($field);
            if (is_null($newValue)) {
                continue;
            }

            $relatedData = $this->getRelationData($model, $relations, $field);
            $oldValueText = '-';

            /**
             * @todo unify this into 1 function code duplicated in delete and edit
             */
            if (!empty($relatedData)) {
                // These means it is a multi level relationship, recursive!
                if (is_array($relatedData['title'])) {
                    $auditModel = $model;
                    $auditData = $relatedData['title'];

                    while (is_array(current($auditData))) {
                        $auditModel = $auditModel->{key($auditData)};
                        $auditData = $auditData[key($auditData)];
                    }

                    foreach ($auditData as &$data) {
                        $data = $auditModel ? $auditModel->{$data} : null;
                    }

                    $newValueText = implode(' ', $auditData);
                } else {
                    $newValueText = is_object($model->{$relatedData['alias']}) ? $model->{$relatedData['alias']}->{$relatedData['title']} : null;
                }

                // These means it is a multi level relationship, recursive!
                if (is_array($relatedData['title'])) {
                    $auditModel = $model;
                    $auditData = $relatedData['title'];

                    $undefiendPropertyBreakLoop = false;

                    while (is_array(current($auditData))) {
                        if (empty($auditModel->{key($auditData)})) {
                            $undefiendPropertyBreakLoop = true;
                            break;
                        }
                        $auditModel = $auditModel->{key($auditData)};
                        $auditData = $auditData[key($auditData)];
                    }

                    $auditModel = $auditModel->findFirst($originalData[$field]);

                    if ($undefiendPropertyBreakLoop) {
                        break;
                    }

                    foreach ($auditData as &$data) {
                        $data = $auditModel->{$data};
                    }

                    $oldValueText = implode(' ', $auditData);
                } else {
                    // Seems like a clean workaround. Will have to keep a tab to check in the future
                    if ($model->{'get' . ucfirst($relatedData['alias'])}()) {
                        $oldValueText = $model->{'get' . ucfirst($relatedData['alias'])}()
                            ->findFirst($originalData[$field])
                            ->{$relatedData['title']};
                    }
                }
            }

            if (!is_null($newValue) && $newValueText != $originalData[$field]) {
                $auditDetail = new AuditsDetails();
                $auditDetail->field_name = $field;
                $auditDetail->old_value = $originalData[$field];
                $auditDetail->old_value_text = $oldValueText;
                $auditDetail->new_value = $newValue;
                $auditDetail->new_value_text = !is_null($newValueText) ? $newValueText : ' ';
                $details[] = $auditDetail;
            }
        }

        if (!empty($model->customFields)) {
            $oldCustomFields = $originalData['customFields'];
            foreach ($model->customFields as $field => $value) {
                if ((array_key_exists($field, $oldCustomFields) && $oldCustomFields[$field] != $value && !empty($value)) || (!array_key_exists($field, $oldCustomFields) && !empty($value))) {
                    $auditDetail = new AuditsDetails();
                    $auditDetail->field_name = $field;
                    $auditDetail->old_value = $oldCustomFields[$field] ?? '';
                    $auditDetail->old_value_text = $oldCustomFields[$field] ?? '';
                    $auditDetail->new_value = $value;
                    $auditDetail->new_value_text = $value;

                    $details[] = $auditDetail;
                }
            }
        }

        if (!empty($details)) {
            $audit = $this->createAudit(self::DELETE, $model);
            $audit->details = $details;
            if (!$audit->save()) {
                $this->log(current($audit->getMessages()));
            }

            return true;
        }

        return false;
    }

    /**
     * Log the info on blameable.
     *
     * @param string $message
     * @return void
     */
    protected function log(string $message): void
    {
        DI::getDefault()->getLog()->error('Saving Blamable ' . $message);
    }

    /**
     * Get the relation data.
     *
     * @param  string $model
     * @param  string $relations
     * @param  string $field
     * @return array
     */
    protected function getRelationData($model, $relations, $field): array
    {
        $auditColumns = $model->getAuditColumns();

        if (array_key_exists($field, $auditColumns)) {
            foreach ($relations as $relation) {
                if ($relation->getFields() == $field) {
                    return [
                        'alias' => $relation->getOptions()['alias'],
                        'title' => $auditColumns[$field]['title'] ?? '',
                    ];
                }
            }
        }

        return [];
    }

    /**
     * @param ModelInterface $model
     */
    protected function collectData(ModelInterface $model): void
    {
        $this->snapshot = $model->getSnapshotData();
        try {
            $this->changedFields = $model->getChangedFields();
        } catch (Throwable $th) {
            $this->changedFields = [];
        }
    }
}
