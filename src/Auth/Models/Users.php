<?php

namespace Baka\Auth\Models;

use Baka\Contracts\Auth\AuthTokenTrait;
use Baka\Contracts\Auth\UserInterface;
use Baka\Contracts\Database\HashTableTrait;
use Baka\Database\Model;
use Baka\Exception\AuthException;
use Baka\Support\Random;
use Baka\Validation as BakaValidation;
use Canvas\Hashing\Password;
use Exception;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;

class Users extends Model implements UserInterface
{
    use AuthTokenTrait;
    use HashTableTrait;

    /**
     * Constant for anonymous user.
     */
    const ANONYMOUS = '-1';

    public ?string $email = null;
    public ?string $password = null;
    public ?string $firstname = null;
    public ?string $lastname = null;
    public ?string $displayname = null;
    public ?string $registered = null;
    public ?string $lastvisit = null;
    public int $default_company = 0;
    public ?string $defaultCompanyName = null;
    public ?string $dob = null;
    public ?string $sex = null;
    public ?string $description = null;
    public ?string $phone_number = null;
    public ?string $cell_phone_number = null;
    public ?string $timezone = null;
    public ?int $city_id = 0;
    public ?int $state_id = 0;
    public ?int $country_id = 0;
    public int $welcome = 0;
    public int $user_active = 0;
    public ?string $user_activation_key = null;
    public ?string $user_activation_email = null;
    public ?string $profile_header = '';
    public bool $loggedIn = false;
    public ?string $location = null;
    public string $interest = '';
    public int $profile_privacy = 0;
    public ?string $user_activation_forgot = null;
    public ?string $language = null;
    public string $session_id = '';
    public string $session_key = '';
    public ?string $banned = null;
    public ?int $user_last_login_try = 0;
    public int $user_level = 0;
    public static string $locale = 'ja_jp';

    /**
     * @deprecated with filesystem
     */
    public ?string $profile_image = null;
    public ?string $profile_image_mobile = null;
    public ?string $profile_remote_image = null;
    public ?string $profile_image_thumb = ' ';

    /**
     * initialize the model.
     */
    public function initialize()
    {
        $this->hasOne('id', 'Baka\Auth\Models\Sessions', 'users_id', ['alias' => 'session']);
        $this->hasMany('id', 'Baka\Auth\Models\Sessions', 'users_id', ['alias' => 'sessions']);
        $this->hasMany('id', 'Baka\Auth\Models\SessionKeys', 'users_id', ['alias' => 'sessionKeys']);
        $this->hasMany('id', 'Baka\Auth\Models\Banlist', 'users_id', ['alias' => 'bans']);
        $this->hasMany('id', 'Baka\Auth\Models\Sessions', 'users_id', ['alias' => 'sessions']);
        $this->hasMany('id', 'Baka\Auth\Models\UserConfig', 'users_id', ['alias' => 'config']);
        $this->hasMany('id', 'Baka\Auth\Models\UserLinkedSources', 'users_id', ['alias' => 'sources']);
        $this->hasMany('id', 'Baka\Auth\Models\UsersAssociatedCompany', 'users_id', ['alias' => 'companies']);
        $this->hasOne('default_company', 'Baka\Auth\Models\Companies', 'id', ['alias' => 'defaultCompany']);
    }

    /**
     * Validations and business logic.
     */
    public function validation()
    {
        $validator = new Validation();
        $validator->add(
            'email',
            new Email([
                'field' => 'email',
                'required' => true,
            ])
        );

        $validator->add(
            'displayname',
            new PresenceOf([
                'field' => 'displayname',
                'required' => true,
            ])
        );

        $validator->add(
            'displayname',
            new Regex([
                'field' => 'displayname',
                'message' => _('Please use alphanumerics only.'),
                'pattern' => '/^[A-Za-z0-9_.-]{1,45}$/',
            ])
        );

        // Unique values
        $validator->add(
            'email',
            new Uniqueness([
                'field' => 'email',
                'message' => _('This email already has an account.'),
            ])
        );

        $validator->add(
            'displayname',
            new Uniqueness([
                'field' => 'displayname',
                'message' => _('The username is already taken.'),
            ])
        );

        return $this->validate($validator);
    }

    /**
     * get the user by its Id, we can specify the cache if we want to
     * we only get result if the user is active.
     *
     * @param int $userId
     * @param bool $cache
     *
     * @return User
     */
    public static function getById($id, $cache = false) : UserInterface
    {
        $options = null;
        $key = 'userInfo_' . $id;

        if ($cache) {
            $options = ['cache' => ['lifetime' => 3600, 'key' => $key]];
        }

        return self::findFirstOrFail([
            'conditions' => 'id = ?0 and is_deleted = 0',
            'bind' => [$id]
        ]);
    }

    /**
     * is the user active?
     *
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->user_active;
    }

    /**
     * get user by there email address.
     *
     * @return User
     */
    public static function getByEmail(string $email) : UserInterface
    {
        $user = self::findFirst([
            'conditions' => 'email = ?0 and is_deleted = 0',
            'bind' => [$email]
        ]);

        if (!$user) {
            throw new Exception('No User Found');
        }

        return $user;
    }

    /**
     * get user nickname.
     *
     * @return string
     */
    public function getDisplayName() : string
    {
        return strtolower($this->displayname);
    }

    /**
     * get user email.
     *
     * @return string
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * is the user logged in?
     *
     * @return bool
     */
    public function isLoggedIn() : bool
    {
        return $this->loggedIn;
    }

    /**
     * Is Anonymous user.
     *
     * @return boolean
     */
    public function isAnonymous() : bool
    {
        return (int) $this->getId() == self::ANONYMOUS;
    }

    /**
     * get the user sex, not get sex from the user :P.
     *
     * @return string
     */
    public function getSex() : string
    {
        if ($this->sex == 'M') {
            return _('Male');
        } elseif ($this->sex == 'F') {
            return _('Female');
        } else {
            return _('Undefined');
        }
    }

    /**
     * Log a user out of the system.
     *
     * @return bool
     */
    public function logOut() : bool
    {
        $session = new Sessions();
        $session->end($this);

        return true;
    }

    /**
     * Clean the user session from the system.
     *
     * @return true
     */
    public function cleanSession() : bool
    {
        $session = new Sessions();
        $session->end($this);

        return true;
    }

    /**
     * Set the setting Hash model.
     *
     * @return void
     */
    protected function createSettingsModel() : void
    {
        $this->settingsModel = new UserConfig();
    }

    /**
     * get the user session id.
     *
     * @return string
     */
    public function getSessionId() : string
    {
        //if its empty get it from the relationship, else get it from the property
        return empty($this->session_id) ? $this->getSession(['order' => 'time desc'])->session_id : $this->session_id;
    }

    /**
     * get the user language.
     *
     * @return string
     */
    public function getLanguage() : ? string
    {
        return $this->language;
    }

    /**
     * Determine if a user is banned.
     *
     * @return bool
     */
    public function isBanned() : bool
    {
        return !$this->isActive() && $this->banned === 'Y';
    }

    /**
     * Update the password for a current user.
     *
     * @param string $newPassword
     *
     * @return bool
     */
    public function updatePassword(string $currentPassword, string $newPassword, string $verifyPassword) : bool
    {
        // Get the current password
        $currentPassword = trim($currentPassword);

        // First off check that the current password matches the current password
        if (password_verify($currentPassword, $this->password)) {
            // Get the new password and the verify
            $newPassword = trim($newPassword);
            $verifyPassword = trim($verifyPassword);

            $data = [
                'new_password' => $newPassword,
                'verify_password' => $verifyPassword,
            ];

            //Ok let validate user password
            $validation = new BakaValidation();
            $validation->add('new_password', new PresenceOf(['message' => 'The password is required.']));

            $validation->add(
                'new_password',
                new StringLength([
                    'min' => 8,
                    'messageMinimum' => 'Password is too short. Minimum 8 characters.',
                ])
            );

            $validation->add('new_password', new Confirmation([
                'message' => 'New password and confirmation do not match.',
                'with' => 'verify_password',
            ]));

            //validate this form for password
            $validation->validate($data);

            // Check that they are the same
            if ($newPassword === $verifyPassword) {
                // Has the password and set it
                $this->password = Password::make($newPassword);

                return true;
            } else {
                throw new AuthException(_('New password and confirmation don\'t match . '));
            }
        }

        throw new AuthException(_(' Your current password is incorrect .'));
    }

    /**
     * Get the current user company.
     */
    public function currentCompanyId() : int
    {
        return 0;
    }

    /**
     * What to do after the creation of a new users
     *  - Company
     *  - add notification for this user.
     *
     * @return void
     */
    public function afterCreate()
    {
        //create company
        $company = new Companies();
        $company->name = $this->defaultCompanyName ?? Random::generateDisplayNameFromEmail($this->email);
        $company->users_id = $this->getId();
        $company->saveOrFail();

        $this->default_company = $company->getId();

        $this->updateOrFail();
    }
}
