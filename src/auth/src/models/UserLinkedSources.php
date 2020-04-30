<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class UserLinkedSources extends Model
{
    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var integer
     */
    public $source_id;

    /**
     *
     * @var integer
     */
    public $source_users_id;

    /**
     *
     * @var string
     */
    public $source_username;

    /**
     * initialize
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
        $this->belongsTo('source_id', 'Baka\Auth\Models\Sources', 'id', ['alias' => 'source']);
    }

    /**
     * Validations and business logic
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
     * Funcion que registra al user de la red social, al sistema.
     * Si ya esta registrado lo logea
     *
     * @param Hybridauth\Entity\Profile $socialProfile
     * @param string $socialNetwork
     * @return Users
     */
    public function associateAccount(Users $user, \Hybridauth\User\Profile $socialProfile, $socialNetwork)
    {
        //si no esta asociada tu uenta
        if (!$this->existSocialProfile($socialProfile, $socialNetwork)) {
            $source = Sources::findFirst(['title = :title:', 'bind' => ['title' => strtolower($socialNetwork)]]);

            $userLinkedSources = new self();
            $userLinkedSources->users_id = $user->getId();
            $userLinkedSources->source_id = $source->source_id;
            $userLinkedSources->source_users_id = $socialProfile->identifier;
            $userLinkedSources->source_username = $socialProfile->identifier;

            //since the user is registration via a social network and it was sucessful we need to activate its account
            if (!$user->user_active) {
                $user->user_active = 1;
                $user->update();
            }

            if (!$userLinkedSources->save()) {
                throw new \Exception($userLinkedSources->getMessages()[0]);
            }

            return true;
        }

        return false;
    }

    /**
     * is this profile already registrated in the system?
     * @param \Hybridauth\Entity\Profile $socialProfile
     * @param string $socialNetwork
     *
     * @return boolean
     */
    public function existSocialProfile(\Hybridauth\User\Profile $socialProfile, $socialNetwork)
    {
        //si existe el source que nos esta pidiendo el usuario
        if ($source = Sources::findFirst(['title = :title:', 'bind' => ['title' => strtolower($socialNetwork)]])) {
            //verificamos que no tenga la cuenta ya relacionada con ese social network
            $bind = [
                'source_id' => $source->source_id,
                'source_users_id' => $socialProfile->identifier,
            ];

            //si no tienes una cuenta ya registrada con social network y no estas registrado con este correo
            if ($userSocialLinked = self::findFirst(['source_id = :source_id: and source_users_id = :source_users_id:', 'bind' => $bind])) {
                $admin = $userSocialLinked->user->isAdmin();
                $userIp = $this->getDI()->getRequest()->getClientAddress();
                $remember = 1;

                //login the user , so we just create the user session base on the user object
                $session = new \Baka\Auth\Models\Sessions();
                $userSession = $session->begin($userSocialLinked->user->getId(), $userIp, PAGE_INDEX, false, $remember, $admin);

                //you are logged in
                return true;
            }
        } else {
            throw new \Exception(_('We currently do not have support to connect to this social network.'));
        }

        return false;
    }

    /**
     * is the user already connecte to the social media site?
     *
     * @param  $userData Users
     * @param  $socialNetwork string
     */
    public static function alreadyConnected(Users $userData, $socialNetwork)
    {
        $source = Sources::findFirst(['title = :title:', 'bind' => ['title' => $socialNetwork]]);

        $bind = [
            'source_id' => $source->source_id,
            'users_id' => $userData->users_id,
        ];

        if ($userSocialLinked = self::findFirst(['source_id = :source_id: and users_id = :users_id:', 'bind' => $bind])) {
            return true;
        }

        return false;
    }
}
