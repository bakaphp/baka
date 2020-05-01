<?php

use Baka\Auth\Models\Users;

class AuthTest extends PhalconUnitTestCase
{

    /**
     * Test userlogin
     *
     * @return boolean
     */
    public function testSessionGenerate()
    {
        $session = new \Baka\Auth\Models\Sessions();
        $request = new \Phalcon\Http\Request();

        $userData = \Baka\Auth\Models\Sessions::start(1, '127.0.0.1');

        $this->assertTrue($userData instanceof Users);
    }

    /**
     * Test user logi
     *
     * @return boolean
     */
    public function testLogin()
    {
        $username = 'kaioken';
        $password = 'nosenose';
        $remember = 1;
        $admin = 0;
        $userIp = '127.0.0.1';

        $userData = Users::login($username, $password, $remember, $admin, $userIp);

        $this->assertTrue($userData instanceof Users);
    }

    /**
     * Logout
     *
     * @return boolean
     */
    public function testLogout()
    {
        $username = 'kaioken';
        $password = 'nosenose';
        $remember = 1;
        $admin = 0;
        $userIp = '127.0.0.1';

        $userData = Users::login($username, $password, $remember, $admin, $userIp);
        $this->assertTrue($userData->logout());
    }

    /**
     * Test user signup
     *
     * @return boolean
     */
    public function testSignUp()
    {
        $user = new Users();

        $randomString = function ($length = 10) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        };

        $user->email = $randomString(10) . '@nose.com';
        $user->password = 'nosenose';
        $user->displayname = $randomString(10);
        if (!$user->signup()) {
            foreach ($user->getMessages() as $message) {
                throw new \Exception($message);
            }
        }

        $this->assertTrue($user instanceof Users);
    }

    /**
     * Teste usser forgout password
     *
     * @return boolean
     */
    public function testForgotPassword()
    {
        $email = 'max@mctekk.com';
        /**
         * check if the user email exist
         * if it does creat the user activation key to send
         * send the user email
         *
         * if it doesnt existe then send the erro msg
         */
        if ($recoverUser = Users::getByEmail($email)) {
            $recoverUser->user_activation_forgot = $recoverUser->generateActivationKey();
            $recoverUser->update();

            return $this->assertTrue(strlen($recoverUser->user_activation_forgot) > 0);
        }

        return $this->assertTrue($recoverUser instanceof Users);
    }

    /**
     * this runs before everyone
     */
    protected function setUp()
    {
        $this->_getDI();

    }

    protected function tearDown()
    {
    }

}
