<?php

namespace Baka\Auth\Models;

use Baka\Contracts\Auth\AuthTokenTrait;
use Baka\Database\Model;
use Exception;
use Locale;
use Phalcon\Http\Request;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;
use stdClass;

class Users extends Model
{
    use AuthTokenTrait;

    /**
     * Constant for anonymous user.
     */
    const ANONYMOUS = '-1';

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $displayname;

    /**
     * @var string
     */
    public $registered;

    /**
     * @var string
     */
    public $lastvisit;

    /**
     * @var int
     */
    public $default_company;
    public $defaultCompanyName;

    /**
     * @var string
     */
    public $dob;

    /**
     * @var string
     */
    public $sex;

    /**
     * @var string
     */
    public $phone_number;

    /**
     * @var string
     */
    public $cell_phone_number;

    /**
     * @var string
     */
    public $timezone;

    /**
     * @var int
     */
    public $city_id;

    /**
     * @var int
     */
    public $state_id;

    /**
     * @var int
     */
    public $country_id;

    /**
     * @var int
     */
    public $welcome = 0;

    /**
     * @var string
     */
    public $profile_image;
    public $profile_image_mobile;

    /**
     * @var string
     */
    public $profile_remote_image;

    /**
     * @var int
     */
    public $user_active;

    /**
     * @var string
     */
    public $user_activation_key;

    /**
     * @var string
     */
    public $user_activation_email;

    public $loggedIn = false;

    public $location = '';

    public $interest = '';

    public $profile_privacy = 0;

    public $profile_image_thumb = ' ';

    public $user_activation_forgot = '';

    public $language;

    public $session_id = '';

    public $session_key = '';

    public $banned;

    public $user_last_login_try;

    public $user_level;
    public $is_deleted = 0;

    public static $locale = 'ja_jp';

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
                'pattern' => '/^[A-Za-z0-9_.-]{1,16}$/',
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
     * get Id.
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
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
    public static function getById($userId, $cache = false) : Users
    {
        $options = null;
        $key = 'userInfo_' . $userId;

        if ($cache) {
            $options = ['cache' => ['lifetime' => 3600, 'key' => $key]];
        }

        if ($userData = self::findFirstById($userId, $options)) {
            return $userData;
        } else {
            throw new Exception(_('The specified user does not exist in our database.'));
        }
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
     * cget the social profile of a users, passing its socialnetwork.
     *
     * @param string $site
     *
     * @return Hybridauth\Entity\Profile
     */
    public static function getSocialProfile($site = 'facebook')
    {
        $config = \Phalcon\DI::getDefault()->getConfig()->social_config->toArray(); //dirname(dirname(dirname(__FILE__ ))) . "/config/social_config.php";
        $hybridauth = new \Hybridauth\Hybridauth($config);

        //$adapter = $hybridauth->authenticate( "Google" );
        $adapter = $hybridauth->authenticate($site);

        // request user profile
        return $adapter->getUserProfile();
    }

    /**
     * logout the user from its social network.
     *
     * @param string $site
     *
     * @return bool
     */
    public static function disconnectSocialProfile($site = 'facebook')
    {
        $config = \Phalcon\DI::getDefault()->getConfig()->social_config->toArray(); //dirname(dirname(dirname(__FILE__ ))) . "/config/social_config.php";
        $hybridauth = new \Hybridauth\Hybridauth($config);
        return $hybridauth->logoutAllProviders();
        //$adapter = $hybridauth->authenticate( "Google" );
        $adapter = $hybridauth->authenticate($site);

        // request user profile
        return $adapter->logout();
    }

    /**
     * Has for the user password.
     *
     * @param string
     *
     * @return string
     */
    public static function passwordHash(string $password) : string
    {
        //cant use it aas a object property cause php sucks and can call a function on a property with a array -_-
        $options = [
            //'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM), // Never use a static salt or one that is not randomly generated.
            'cost' => 12, // the default cost is 10
        ];

        $hash = password_hash($password, PASSWORD_DEFAULT, $options);

        return $hash;
    }

    /**
     * Check if the user password needs to ve rehash
     * why? php shit with the new API http://www.php.net/manual/en/function.password-needs-rehash.php.
     *
     * @param string $password
     *
     * @return bool
     */
    public function passwordNeedRehash(string $password) : bool
    {
        $options = [
            //'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM), // Never use a static salt or one that is not randomly generated.
            'cost' => 12, // the default cost is 10
        ];

        if (password_needs_rehash($this->password, PASSWORD_DEFAULT, $options)) {
            $this->password = self::passwordHash($password);
            $this->update();

            return true;
        }

        return false;
    }

    /**
     * get user by there email address.
     *
     * @return User
     */
    public static function getByEmail(string $email) : Users
    {
        $user = self::findFirst([
            'conditions' => 'email = ?0',
            'bind' => [$email]
        ]);

        if (!$user) {
            throw new Exception('No User Found');
        }

        return $user;
    }

    /**
     * get the user profileHeader.
     *
     * @param bool $mobile
     *
     * @return string
     */
    public function getProfileHeader(bool $mobile = false) : ? string
    {
        //$this->cdn
        $cdn = \Phalcon\DI::getDefault()->getCdn() . '/profile_headers/';
        $header = null;
        $image = !$mobile ? $this->profile_header : $this->profile_header_mobile;

        if (!empty($this->profile_header)) {
            $header = $cdn . $image;
        }

        return $header;
    }

    /**
     * get the user avatar.
     *
     * @return string
     */
    public function getAvatar() : ? string
    {
        //$this->cdn
        $cdn = \Phalcon\DI::getDefault()->getCdn() . '/avatars/';
        $avatar = $cdn . 'nopicture.png';

        if (!empty($this->profile_image)) {
            $avatar = $cdn . $this->profile_image;
        } elseif (!empty($this->profile_remote_image)) {
            $avatar = $this->profile_remote_image;
        }

        return $avatar;
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
    public function getEmail() : string
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
     * is thie user admin level?
     *
     * @return bool
     */
    public function isAdmin() : bool
    {
        return (int)$this->user_level === 1;
    }

    /**
     * Determine if the user is a moderator.
     *
     * @return bool
     */
    public function isModerator() : bool
    {
        return $this->isAdmin();
    }

    /**
     * Generate a user activation key.
     *
     * @return string
     */
    public function generateActivationKey() : string
    {
        return sha1(mt_rand(10000, 99999) . time() . $this->email);
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
     * Give the user a order array with the user configuration.
     */
    public function getConfig() : array
    {
        $redis = $this->getDI()->getRedis();

        //get if from redis first
        if (!empty($redisConfig = $redis->hGetAll($this->getNotificationKey()))) {
            return $redisConfig;
        }

        $config = [];
        $userConfiguration = $this->getConfigs(['hydration' => \Phalcon\Mvc\Model\Resultset::HYDRATE_ARRAYS]);

        foreach ($userConfiguration as $value) {
            $config[$value['name']] = $value['value'];
        }

        return $config;
    }

    /**
     * get the obj of the current user config.
     *
     * @return UserConfig
     */
    public function config() : UserConfig
    {
        $config = new UserConfig();
        $config->users_id = $this->getId();

        return $config;
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
     * does the user as the configuration on?
     *
     * @param  $key string
     *
     * @return bool
     */
    public function hasConfig(string $key)
    {
        $redis = $this->getDI()->getRedis();
        $hashKey = $this->getNotificationKey(); //'user_notifications_'.$this->user_id;

        return $redis->hGet($hashKey, $key);
    }

    /**
     * get the user language.
     *
     * @return string
     */
    public function getLanguage(bool $short = false) : ? string
    {
        $request = new Request();

        if ($this->isLoggedIn() && !empty($this->language)) {
            $lang = !$short ? strtolower($this->language) . '_' . $this->language : strtolower($this->language);
        } elseif ($this->getDI()->getSession()->has('requestLanguage')) {
            $lang = !$short ? $this->getDI()->getSession()->get('requestLanguage') . '_' . strtoupper($this->getDI()->getSession()->get('requestLanguage')) : strtolower($this->getDI()->getSession()->get('requestLanguage'));
        } else {
            if (!is_null($request->getServer('HTTP_ACCEPT_LANGUAGE'))) {
                $lang = !$short ? Locale::acceptFromHttp($request->getServer('HTTP_ACCEPT_LANGUAGE')) : strtolower(Locale::acceptFromHttp($request->getServer('HTTP_ACCEPT_LANGUAGE')));
            } else {
                $lang = null;
            }
        }

        return $lang;
    }

    /**
     * Get the language user prefix.
     *
     * @return string
     */
    public function getLanguageUrl() : ? string
    {
        if (strtolower($this->getLanguage()) == 'es_es') {
            return '/es';
        }

        return null;
    }

    /**
     * Is the user using the spanish langugue?
     *
     * @return bool
     */
    public function usingSpanish() : bool
    {
        if (strtolower($this->getLanguage()) == 'es_es') {
            return true;
        }

        return false;
    }

    /**
     * Determine if a user is banned.
     *
     * @return bool
     */
    public function isBanned() : bool
    {
        if ($this->banned == 'Y') {
            return true;
        }

        return false;
    }

    /**
     * Given a firstname give me a random username.
     *
     * @param string $displayname
     * @param int $randNo
     *
     * @return string
     */
    protected function generateDisplayName(string $displayname, $randNo = 200) : string
    {
        $usernameParts = array_filter(explode(' ', strtolower($displayname))); //explode and lowercase name
        $usernameParts = array_slice($usernameParts, 0, 2); //return only first two arry part

        $part1 = (!empty($usernameParts[0])) ? substr($usernameParts[0], 0, 8) : ''; //cut first name to 8 letters
        $part2 = (!empty($usernameParts[1])) ? substr($usernameParts[1], 0, 5) : ''; //cut second name to 5 letters
        $part3 = ($randNo) ? rand(0, $randNo) : '';

        $username = $part1 . str_shuffle($part2) . $part3; //str_shuffle to randomly shuffle all characters
        return $username;
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
            $validation = new Validation();
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
            $messages = $validation->validate($data);
            if (count($messages)) {
                foreach ($messages as $message) {
                    throw new Exception($message);
                }
            }

            // Check that they are the same
            if ($newPassword === $verifyPassword) {
                // Has the password and set it
                $this->password = self::passwordHash($newPassword);

                return true;
            } else {
                throw new Exception(_('New password and confirmation don\'t match . '));
            }
        }

        throw new Exception(_(' Your current password is incorrect .'));
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
        $company->name = $this->defaultCompanyName;
        $company->users_id = $this->getId();
        $company->saveOrFail();

        $this->default_company = $company->getId();

        $this->updateOrFail();
    }
}
