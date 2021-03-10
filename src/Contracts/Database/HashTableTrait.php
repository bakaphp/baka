<?php

declare(strict_types=1);

namespace Baka\Contracts\Database;

use function Baka\isJson;
use RuntimeException;

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
    private function getSettingsPrimaryKey() : string
    {
        return $this->getSource() . '_' . $this->getPrimaryKey();
    }

    /**
     * Set the setting model.
     *
     * @return void
     */
    protected function createSettingsModel() : void
    {
        $class = get_class($this) . 'Settings';

        $this->settingsModel = new $class();
    }

    /**
     * Set the settings.
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function set(string $key, $value)
    {
        $this->createSettingsModel();

        if (!is_object($this->settingsModel)) {
            throw new RuntimeException('ModelSettingsTrait need to have a settings model configure, check the model setting exists for this class' . get_class($this));
        }

        //if we don't find it we create it
        if (empty($this->settingsModel = $this->getSettingsByKey($key))) {
            /**
             * @todo this is stupid look for a better solution
             */
            $this->createSettingsModel();
            $this->settingsModel->{$this->getSettingsPrimaryKey()} = $this->getId();
        }

        $this->settingsModel->name = $key;
        $this->settingsModel->value = !is_array($value) ? (string) $value : json_encode($value);
        $this->settingsModel->saveOrFail();

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
    public function getAllSettings() : array
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
     *
     * @return mixed
     */
    public function get(string $key)
    {
        $this->createSettingsModel();
        $value = $this->getSettingsByKey($key);

        if (is_object($value)) {
            return !isJson($value->value) ? $value->value : json_decode($value->value, true);
        }

        return null;
    }

    /**
     * Delete element.
     *
     * @param string $key
     *
     * @return bool
     */
    public function deleteHash(string $key) : bool
    {
        $this->createSettingsModel();
        if ($record = $this->getSettingsByKey($key)) {
            return $record->delete();
        }

        return false;
    }
}
