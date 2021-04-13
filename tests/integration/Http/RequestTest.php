<?php

namespace Baka\Test\Integration\Http;

use Baka\Http\Request\Phalcon;
use Exception;
use PhalconUnitTestCase;

class RequestTest extends PhalconUnitTestCase
{
    public function testPostValidation()
    {
        $_POST = [
            'username' => 'kaioken',
            'email' => 'test@test.com',
            'password' => 'something',
            'confirm_password' => 'something',
            'nested' => [
                'object' => 'true'
            ]
        ];

        $request = new Phalcon();

        $validate = $request->validate([
            'username' => 'required|alpha_dash',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
            'nested.object' => 'required'
        ]);

        $this->assertFalse($validate->fails());
    }

    public function testPostValidationFail()
    {
        $_POST = [
            'username' => 1111,
            'email' => 'dfafad',
        ];

        $request = new Phalcon();

        try {
            $request->validate([
                'username' => 'required|alpha_dash',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password'
            ]);
        } catch (Exception $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());
            $this->assertIsArray($e->getData());
        }
    }
}
