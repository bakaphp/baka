<?php

namespace Baka\Test\Integration\Auth;

use Baka\Auth\Models\Users;
use PhalconUnitTestCase;

class AuthTest extends PhalconUnitTestCase
{
    /**
     * Test user signup.
     *
     * @return boolean
     */
    public function testSignUp()
    {
        $user = new Users();

        $user->email = $this->faker->email;
        $user->password = 'nonenone';
        $user->name = $this->faker->name;
        $user->defaultCompanyName = $this->faker->name;
        $user->displayname = $this->faker->firstname;

        if (!$user->signup()) {
            foreach ($user->getMessages() as $message) {
                throw new \Exception($message);
            }
        }

        $this->assertTrue($user instanceof Users);
    }

    /**
     * Test userlogin.
     *
     * @return boolean
     */
    public function testSessionGenerate()
    {
        $session = new \Baka\Auth\Models\Sessions();

        $userData = $session->start(
            Users::findFirst(),
            $this->faker->uuid,
            $this->faker->sha256,
            $this->faker->ipv4,
            1
        );


        $this->assertTrue($userData instanceof Users);
    }

    /**
     * Test user logi.
     *
     * @return boolean
     */
    public function testLogin()
    {
        $user = Users::findFirst();

        $email = $user->email;
        $password = 'nonenone';
        $remember = 1;
        $admin = 0;
        $userIp = $this->faker->ipv4;

        $userData = Users::login($email, $password, $remember, $admin, $userIp);

        $this->assertTrue($userData instanceof Users);
    }

    /**
     * Logout.
     *
     * @return boolean
     */
    public function testLogout()
    {
        $user = Users::findFirst();

        $email = $user->email;
        $password = 'nonenone';
        $remember = 1;
        $admin = 0;
        $userIp = $this->faker->ipv4;

        $userData = Users::login($email, $password, $remember, $admin, $userIp);
        $this->assertTrue($userData->logout());
    }

    /**
     * Teste usser forgout password.
     *
     * @return boolean
     */
    public function testForgotPassword()
    {
        $user = Users::findFirst();

        $email = $user->email;
        /**
         * check if the user email exist
         * if it does create the user activation key to send
         * send the user email.
         *
         * if it doesn't exist then send the error msg
         */
        if ($recoverUser = Users::getByEmail($email)) {
            $recoverUser->user_activation_forgot = $recoverUser->generateActivationKey();
            $recoverUser->update();

            return $this->assertTrue(strlen($recoverUser->user_activation_forgot) > 0);
        }

        return $this->assertTrue($recoverUser instanceof Users);
    }
}
