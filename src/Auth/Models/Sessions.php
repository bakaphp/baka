<?php

/**
 * JWT Session Token Manager.
 */

namespace Baka\Auth\Models;

use Baka\Contracts\Auth\AuthTokenTrait;
use Baka\Database\Model;
use Exception;

class Sessions extends Model
{
    use AuthTokenTrait;

    public int $users_id;
    public string $token;
    public string $start;
    public string  $time;
    public string $ip;
    public string $page;
    public int $logged_in;

    /**
     * Initialize.
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
    }

    /**
     * Create a new session token for the given users, to track on the db.
     *
     * @param Users $user
     * @param string $sessionId
     * @param string $token
     * @param string $userIp
     * @param int $pageId
     *
     * @return Users
     */
    public function start(Users $user, string $sessionId, string $token, string $userIp, int $pageId) : Users
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
         *
         * @todo we don't need a new session for every getenv('ANONYMOUS') user, use less ,
         * right now 27.7.15 90% of the sessions are for that type of users
         */
        $session = new self();
        $session->users_id = $user->getId();
        $session->start = $currentTime;
        $session->time = $currentTime;
        $session->page = $pageId;
        $session->logged_in = 1;
        $session->id = $sessionId;
        $session->token = $token;
        $session->ip = $userIp;
        $session->saveOrFail();

        $lastVisit = ($user->session_time > 0) ? $user->session_time : $currentTime;

        //update user info
        $user->session_time = $currentTime;
        $user->session_page = $pageId;
        $user->lastvisit = date('Y-m-d H:i:s', $lastVisit);
        $user->updateOrFail();

        //create a new one
        $session = new SessionKeys();
        $session->sessions_id = $sessionId;
        $session->users_id = $user->getId();
        $session->last_ip = $userIp;
        $session->last_login = $currentTime;
        $session->saveOrFail();

        //you are in, no?
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
     * @param int $pageId
     *
     * @return Users
     */
    public function check(Users $user, string $sessionId, string $userIp, int $pageId) : Users
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
     * @param bool $daemon
     *
     * @return void
     */
    public function clean() : bool
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
     *
     * @return bool
     */
    public function end(Users $user) : bool
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
     *
     * @return array
     */
    public static function restart(Users $user, string $sessionId, string $clientAddress) : array
    {
        $session = new self();
        $session->check($user, $sessionId, $clientAddress, 1);
        $token = self::createJwtToken($sessionId, $user->email);
        $session->start($user, $token['sessionId'], $token['token'], $clientAddress, 1);
        return $token;
    }
}
