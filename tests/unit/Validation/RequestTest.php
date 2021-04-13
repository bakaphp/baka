<?php

declare(strict_types=1);

namespace Baka\Test\Unit\Validation;

use Baka\Validations\Request;
use PhalconUnitTestCase;

class RequestTest extends PhalconUnitTestCase
{
    public function testRegularValidation()
    {
        $requestValidation = new Request();

        $post = [
            'username' => 1111,
            'email' => 'dfafad',
        ];

        $validator = $requestValidation->make($post, [
            'username' => 'required|alpha_dash',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertIsArray($validator->errors()->getMessages());
    }

    public function testPassingValidation()
    {
        $requestValidation = new Request();

        $post = [
            'username' => 1111,
            'email' => 'test@test.com',
            'password' => 'something',
            'confirm_password' => 'something'
        ];

        $validator = $requestValidation->make($post, [
            'username' => 'required|alpha_dash',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password'
        ]);

        $this->assertFalse($validator->fails());
        $this->assertIsArray($validator->errors()->getMessages());
    }

    public function testPassingValidationWithSingleton()
    {
        $requestValidation = Request::getInstance();

        $post = [
            'username' => 1111,
            'email' => 'test@test.com',
            'password' => 'something',
            'confirm_password' => 'something'
        ];

        $validator = $requestValidation->make($post, [
            'username' => 'required|alpha_dash',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password'
        ]);

        $this->assertFalse($validator->fails());
        $this->assertIsArray($validator->errors()->getMessages());
    }

    public function testFailValidationWithSingleton()
    {
        $requestValidation = Request::getInstance();

        $post = [
            'username' => 1111,
            'email' => 'dfafad',
        ];

        $validator = $requestValidation->make($post, [
            'username' => 'required|alpha_dash',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertIsArray($validator->errors()->getMessages());
    }
}
