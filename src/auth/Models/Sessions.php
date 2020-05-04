<?php

/**
 * JWT Session Token Manager.
 */

namespace Baka\Auth\Models;

use Baka\Database\Model;
use Exception;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha512;

class Sessions extends Model
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var integer
     */
    public $users_id;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $start;

    /**
     * @var integer
     */
    public $time;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $page;

    /**
     * @var string
     */
    public $logged_in;

    /**
     * @var string
     */
    public $is_admin;

    /**
     * almecenamos la info del usuario par ahacer singlation.
     *
     * @var user
     */
    public static $userData = null;

    public $config;

    /**
     * Initialize.
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
        $this->hasMany('id', 'Baka\Auth\Models\Users', 'sessions_id', ['alias' => 'sessionKeys']);
    }

    /**
     * Create a new session token for the given users, to track on the db.
     *
     * @param Users $user
     * @param string $sessionId
     * @param string $token
     * @param string $userIp
     * @param integer $pageId
     * @return Users
     */
    public function start(Users $user, string $sessionId, string $token, string $userIp, int $pageId): Users
    {
        $last_visit = 0;
        $currentTime = time();

        //
        // Initial ban check against user id, IP and email address
        //
        preg_match('/(..)(..)(..)(..)/', $userIp, $userIp_parts);

        $sql = "SELECT ip, users_id, email
            FROM  Baka\Auth\Models\Banlist
            WHERE ip IN ('" . $userIp_parts[1] . $userIp_parts[2] . $userIp_parts[3] . $userIp_parts[4] . "', '" . $userIp_parts[1] . $userIp_parts[2] . $userIp_parts[3] . "ff', '" . $userIp_parts[1] . $userIp_parts[2] . "ffff', '" . $userIp_parts[1] . "ffffff')
                OR users_id = :users_id:";

        $sql .= " OR email LIKE '" . str_replace("\'", "''", $user->email) . "'
                OR email LIKE '" . substr(str_replace("\'", "''", $user->email), strpos(str_replace("\'", "''", $user->email), '@')) . "'";

        $params = [
            'users_id' => $user->getId(),
        ];

        $result = $this->getModelsManager()->executeQuery($sql, $params);

        //user ban info
        $banData = $result->toArray();
        $banInfo = count($banData) > 0 ? $banData[0] : null;

        if ($banInfo) {
            if ($banInfo['ip'] || $banInfo['users_id'] || $banInfo['email']) {
                throw new Exception(_('This account has been banned. Please contact the administrators.'));
            }
        }

        /**
         * Create or update the session.
         * @todo we dont need a new session for every getenv('ANONYMOUS') user, use less ,
         * right now 27.7.15 90% of the sessions are for that type of users
         */
        $session = new self();
        $session->users_id = $user->getId();
        $session->start = $currentTime;
        $session->time = $currentTime;
        $session->page = $pageId;
        $session->logged_in = 1;
        $session->is_admin = (int) $user->isAdmin();
        $session->id = $sessionId;
        $session->token = $token;
        $session->ip = $userIp;

        if (!$session->save()) {
            throw new Exception(current($session->getMessages()));
        }

        $lastVisit = ($user->session_time > 0) ? $user->session_time : $currentTime;

        //update user info
        $user->session_time = $currentTime;
        $user->session_page = $pageId;
        $user->lastvisit = date('Y-m-d H:i:s', $lastVisit);
        $user->update();

        //create a new one
        $session = new SessionKeys();
        $session->sessions_id = $sessionId;
        $session->users_id = $user->getId();
        $session->last_ip = $userIp;
        $session->last_login = $currentTime;
        $session->save();

        if (!$session->save()) {
            throw new Exception(current($session->getMessages()));
        }

        //you are looged in, no?
        $user->loggedIn = true;

        return $user;
    }

    /**
     * Checks for a given user session, tidies session table and updates user
     * sessions at each page refresh.
     *
     * @param Users $user
     * @param string $sessionId
     * @param string $userIp
     * @param integer $pageId
     * @return Users
     */
    public function check(Users $user, string $sessionId, string $userIp, int $pageId): Users
    {
        $currentTime = time();

        $pageId = (int) $pageId;

        //
        // session_id exists so go ahead and attempt to grab all
        // data in preparation
        //
        $sql = "SELECT user.*, session.*
                FROM Baka\Auth\Models\Sessions session, Baka\Auth\Models\Users user
                WHERE session.id = :session_id:
                    AND user.id = session.users_id";

        $result = $this->getModelsManager()->createQuery($sql);
        $result = $result->execute([
            'session_id' => $sessionId,
        ]);

        //session data
        $userData = $result->getFirst();

        if (empty($userData)) {
            throw new Exception('Invalid Session');
        }

        //wtf? how did you get this token to mimic another user?
        if ($userData->user->getId() != $user->getId()) {
            throw new Exception('Invalid Token');
        }

        //
        // Did the session exist in the DB?
        //
        if ($userData->user) {
            // Only update session DB a minute or so after last update
            if ($currentTime - $userData->session->time > 60) {
                //update the user session
                $session = self::findFirstById($sessionId);
                $session->session_time = $currentTime;
                $session->session_page = $pageId;

                if (!$session->update()) {
                    throw new Exception(current($session->getMessages()));
                }

                //update user
                $user->users_id = $userData->user->getId();
                $user->session_time = $currentTime;
                $user->session_page = $pageId;
                $user->update();

                //$this->clean($sessionId);
            }

            $user->session_id = $sessionId;

            return $user;
        }

        throw new Exception(_('No Session Token Found'));
    }

    /**
     * Removes expired sessions and auto-login keys from the database.
     *
     * @param boolean $daemon
     * @return void
     */
    public function clean(): bool
    {
        //
        // Delete expired sessions
        //
        $sql = "DELETE FROM  Baka\Auth\Models\Sessions
            WHERE time < :session_time:";

        $sessionTime = time() - (int) $this->getDI()->getConfig()->jwt->payload->exp;

        $params = [
            'session_time' => $sessionTime,
        ];

        $result = $this->getModelsManager()->executeQuery($sql, $params);

        //
        // Delete expired keys
        //
        $sql = 'DELETE FROM Baka\Auth\Models\SessionKeys
                WHERE last_login < :last_login: ';

        $last_login = time() - (2 * (int)$this->getDI()->getConfig()->jwt->payload->exp);

        $params = ['last_login' => $last_login];

        $result = $this->getModelsManager()->executeQuery($sql, $params);

        return true;
    }

    /**
     * Terminates the specified session
     * It will delete the entry in the sessions table for this session,
     * remove the corresponding auto-login key and reset the cookies.
     *
     * @param Users $user
     * @return boolean
     */
    public function end(Users $user): bool
    {
        $this->find('users_id = ' . $user->getId())->delete();
        SessionKeys::find('users_id = ' . $user->getId())->delete();

        return true;
    }

    /**
     * Check auth session status and create a new one if there is none.
     *
     * @param Users $user
     * @param string $sessionId
     * @param string $clientAddress
     * @return array
     */
    public static function restart(Users $user, string $sessionId, string $clientAddress): array
    {
        $session = new self();
        $session->check($user, $sessionId, $clientAddress, 1);
        $token = self::refresh($sessionId, $user->email);
        $session->start($user, $token['sessionId'], $token['token'], $clientAddress, 1);
        return $token;
    }

    /**
     * Create a new session based off the refresh token session id.
     *
     * @param string $sessionId
     * @param string $email
     * @return array
     */
    public function refresh(string $sessionId, string $email): array
    {
        $signer = new Sha512();
        $builder = new Builder();
        $token = $builder
            ->setIssuer(getenv('TOKEN_AUDIENCE'))
            ->setAudience(getenv('TOKEN_AUDIENCE'))
            ->setId($sessionId, true)
            ->setIssuedAt(time())
            ->setNotBefore(time() + 500)
            ->setExpiration(time() + getenv('APP_JWT_SESSION_EXPIRATION'))
            ->set('sessionId', $sessionId)
            ->set('email', $email)
            ->sign($signer, getenv('TOKEN_PASSWORD'))
            ->getToken();
        return [
            'sessionId' => $sessionId,
            'token' => $token->__toString()
        ];
    }
}
