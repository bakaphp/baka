<?php

declare(strict_types=1);

namespace Baka\Auth;

use Baka\Auth\Models\Users;
use Baka\Contracts\Auth\UserInterface;
use Baka\Exception\AuthException;
use Baka\Hashing\Keys;
use Baka\Hashing\Password;
use Baka\Support\Random;
use Exception;
use stdClass;

class Auth
{
    /**
     * User login.
     *
     * @param string $email
     * @param string $password
     * @param int $autologin
     * @param int $admin
     * @param string $userIp
     *
     * @return Users
     */
    public static function login(string $email, string $password) : UserInterface
    {
        //trim email
        $email = ltrim(trim($email));
        $password = ltrim(trim($password));

        //if its a email lets by it by email, if not by displayname
        $user = UserProvider::get()::getByEmail($email);

        //first we find the user
        if (!$user) {
            throw new AuthException(_('Invalid Username or Password.'));
        }

        self::loginAttemptsValidation($user);

        //password verification
        if (Password::check($password, $user->password) && $user->isActive()) {
            //rehash password
            Password::rehash($password, $user);

            // Reset login tries
            self::resetLoginTries($user);
            return $user;
        } elseif ($user->isActive()) {
            // Only store a failed login attempt for an active user - inactive users can't login even with a correct password
            self::updateLoginTries($user);

            throw new AuthException(_('Invalid Username or Password..'));
        } elseif ($user->isBanned()) {
            throw new AuthException(_('User has not been banned, please check your email for the activation link.'));
        } else {
            throw new AuthException(_('User has not been activated, please check your email for the activation link.'));
        }
    }

    /**
     * Create a new user.
     *
     * @return Users
     */
    public static function signUp(array $userData) : UserInterface
    {
        $user = UserProvider::get();
        $user->email = $userData['email'];
        $user->sex = 'U';
        $user->firstname = $userData['firstname'] ?? ' ';
        $user->lastname = $userData['lastname'] ?? ' ';
        $user->displayname = $userData['displayname'] ?? Random::generateDisplayName($userData['email']);
        $user->dob = date('Y-m-d');
        $user->lastvisit = date('Y-m-d H:i:s');
        $user->registered = date('Y-m-d H:i:s');
        $user->timezone = 'America/New_York';
        $user->user_level = 3;
        $user->user_active = 1;
        $user->status = 1;
        $user->banned = 'N';
        $user->user_login_tries = 0;
        $user->user_last_login_try = 0;
        $user->default_company = $userData['default_company'] ?? 0;
        $user->session_time = time();
        $user->session_page = time();
        $user->password = Password::make($userData['password']);
        $user->language = $userData['language'] ?? 'EN';
        $user->user_activation_key = Keys::make();

        //if you need to run any extra feature with the data we get from the request
        if (method_exists($user, 'setCustomFields')) {
            $user->setCustomFields($userData);
        }

        $user->saveOrFail();

        return $user;
    }

    /**
     * Check the user login attempt to the app.
     *
     * @param Users $user
     *
     * @throws Exception
     *
     * @return void
     */
    protected static function loginAttemptsValidation(UserInterface $user) : bool
    {
        //load config
        $config = new stdClass();
        $config->login_reset_time = getenv('AUTH_MAX_AUTOLOGIN_TIME');
        $config->max_login_attempts = getenv('AUTH_MAX_AUTOLOGIN_ATTEMPS');

        // If the last login is more than x minutes ago, then reset the login tries/time
        if ($user->user_last_login_try && $config->login_reset_time && $user->user_last_login_try < (time() - ($config->login_reset_time * 60))) {
            $user->user_login_tries = 0; //turn back to 0 attems, succes
            $user->user_last_login_try = 0;
            $user->updateOrFail();
        }

        // Check to see if user is allowed to login again... if his tries are exceeded
        if ($user->user_last_login_try
            && $config->login_reset_time
            && $config->max_login_attempts
            && $user->user_last_login_try >= (time() - ($config->login_reset_time * 60))
            && $user->user_login_tries >= $config->max_login_attempts) {
            throw new AuthException(sprintf(_('You have exhausted all login attempts.'), $config->max_login_attempts));
        }

        return true;
    }

    /**
     * Reset login tries.
     *
     * @param Users $user
     *
     * @return bool
     */
    protected static function resetLoginTries(UserInterface $user) : bool
    {
        $user->lastvisit = date('Y-m-d H:i:s');
        $user->user_login_tries = 0;
        $user->user_last_login_try = 0;
        return $user->updateOrFail();
    }

    /**
     * Update login tries for the given user.
     *
     * @return bool
     */
    protected static function updateLoginTries(UserInterface $user) : bool
    {
        if ($user->getId() != Users::ANONYMOUS) {
            $user->user_login_tries += 1;
            $user->user_last_login_try = time();
            return $user->updateOrFail();
        }

        return false;
    }
}
