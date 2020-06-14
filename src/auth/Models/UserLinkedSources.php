<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class UserLinkedSources extends Model
{
    public int $users_id;
    public int $source_id;
    public int $source_users_id;
    public string $source_users_id_text;
    public string $source_username;

    /**
     * initialize.
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
        $this->belongsTo('source_id', 'Baka\Auth\Models\Sources', 'id', ['alias' => 'source']);
    }

    /**
     * Validations and business logic.
     */
    public function validation()
    {
        $validator = new Validation();
        $validator->add(
            [
                'users_id',
                'source_id'
            ],
            new Uniqueness([
                'field' => ['users_id', 'source_id'],
                'message' => _('You have already associated this account.'),
            ])
        );
        return $this->validate($validator);
    }

    /**
     * is the user already connected to the social media site?
     *
     * @param  $userData Users
     * @param  $socialNetwork string
     */
    public static function alreadyConnected(Users $userData, $socialNetwork) : bool
    {
        $source = Sources::findFirst(['title = :title:', 'bind' => ['title' => $socialNetwork]]);

        $bind = [
            'source_id' => $source->source_id,
            'users_id' => $userData->users_id,
        ];

        if (self::findFirst(['source_id = :source_id: and users_id = :users_id:', 'bind' => $bind])) {
            return true;
        }

        return false;
    }
}
