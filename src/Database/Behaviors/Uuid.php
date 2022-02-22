<?php
declare(strict_types=1);

namespace Baka\Database\Behaviors;

use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Security\Random;

class Uuid extends Behavior
{
    /**
     * Behavior implementation for Uuid.
     *
     * @param string $eventType
     * @param ModelInterface $model
     *
     * @return void
     */
    public function notify(string $eventType, ModelInterface $model) : void
    {
        $random = new Random();

        $options = $this->getOptions();
        $field = $options['field'] ?? 'uuid';

        switch ($eventType) {
            case 'beforeValidationOnCreate':
                $model->writeAttribute($field, $random->uuid());
                break;
        }
    }
}
