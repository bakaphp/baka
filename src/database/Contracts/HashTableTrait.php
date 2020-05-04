<?php

declare(strict_types=1);

namespace Baka\Database\Contracts;

use RuntimeException;
use Baka\Database\Exception\Exception;

/**
 * Trait ResponseTrait.
 *
 * @package Gewaer\Traits
 *
 * @property Users $user
 * @property Config $config
 * @property Request $request
 * @property Auth $auth
 * @property \Phalcon\Di $di
 *
 */
trait HashTableTrait
{
    protected $settingsModel;

    /**
     * get the primary key.
     *
     * @return void
     */
    abstract protected function getPrimaryKey();

    /**
     * Get the primary key of this model, this will only work on model with just 1 primary key.
     *
     * @return string
     */
    private function getSettingsPrimaryKey(): string
    {
        return $this->getSource() . '_' . $this->getPrimaryKey();
    }

    /**
     * Set the setting model.
     *
     * @return void
     */
    private function createSettingsModel(): void
    {
        $class = get_class($this) . 'Settings';

        $this->settingsModel = new $class();
    }

    /**
     * Set the settings.
     *
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function set(string $key, $value) : bool
    {
        $this->createSettingsModel();

        if (!is_object($this->settingsModel)) {
            throw new RuntimeException('ModelSettingsTrait need to have a settings model configure, check the model setting existe for this class' . get_class($this));
        }

        //if we dont find it we create it
        if (empty($this->settingsModel = $this->getSettingsByKey($key))) {
            /**
             * @todo this is stupid look for a better solution
             */
            $this->createSettingsModel();
            $this->settingsModel->{$this->getSettingsPrimaryKey()} = $this->getId();
        }

        $this->settingsModel->name = $key;
        $this->settingsModel->value = $value;
        if (!$this->settingsModel->save()) {
            throw new Exception((string)current($this->settingsModel->getMessages()));
        }

        return true;
    }

    /**
     * Get the settings by its key.
     */
    protected function getSettingsByKey(string $key)
    {
        return $this->settingsModel->findFirst([
            'conditions' => "{$this->getSettingsPrimaryKey()} = ?0 and name = ?1",
            'bind' => [$this->getId(), $key]
        ]);
    }

    /**
     * Get all the setting of a given record.
     *
     * @return array
     */
    public function getAllSettings(): array
    {
        $this->createSettingsModel();

        $allSettings = [];
        $settings = $this->settingsModel->find([
            'conditions' => "{$this->getSettingsPrimaryKey()} = ?0",
            'bind' => [$this->getId()]
        ]);

        foreach ($settings as $setting) {
            $allSettings[$setting->name] = $setting->value;
        }

        return $allSettings;
    }

    /**
     * Get the settings base on the key.
     *
     * @param string $key
     * @return void
     */
    public function get(string $key): ?string
    {
        $this->createSettingsModel();
        $value = $this->getSettingsByKey($key);

        if (is_object($value)) {
            return $value->value;
        }

        return null;
    }
}
