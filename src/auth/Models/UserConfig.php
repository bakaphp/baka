<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;
use Exception;

class UserConfig extends Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=20, nullable=false)
     */
    public $users_id;

    /**
     *
     * @var string
     * @Primary
     * @Column(type="string", length=45, nullable=false)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    public $value;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $added_date;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $updated_date;

    /**
     * Initialize
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
    }

    /**
     * get a value to the table
     *
     * @param string $key
     * @param string $value
     */
    public function get(string $key): string
    {
        if (!$this->users_id) {
            throw new Exception('No users is set to save config');
        }

        if ($config = $this->findFirst(['conditions' => 'users_id = ?0 and name = ?1', 'bind' => [$this->users_id, $key]])) {
            return $config->value;
        }

        throw new Exception('No value found for key: ' . $key);
    }

    /**
     * Set a value to the table
     *
     * @param string $key
     * @param string $value
     */
    public function set(string $key, string $value): bool
    {
        if (!$this->users_id) {
            throw new Exception('No users is set to save config');
        }

        $config = new self();
        $config->users_id = $this->users_id;
        $config->name = $key;
        $config->value = $value;
        if (!$config->save()) {
            throw new Exception(current($config->getMessages()));
        }

        return true;
    }

    /**
     * Set a value to the table
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function deleteByKey(string $key): bool
    {
        if (!$this->users_id) {
            throw new Exception('No users is set to save config');
        }

        if ($config = $this->findFirst(['conditions' => 'users_id = ?0 and name = ?1', 'bind' => [$this->users_id, $key]])) {
            return $config->delete();
        }

        return false;
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'user_config';
    }
}
