<?php

declare(strict_types=1);

namespace Baka\Test\Unit\Http\Request;

use Baka\Http\Request\Phalcon;
use Baka\Test\Support\Http\PhpStream;
use function file_put_contents;
use function json_encode;
use PhalconUnitTestCase;
use function stream_wrapper_register;
use function stream_wrapper_unregister;

class PhalconTest extends PhalconUnitTestCase
{
    public function testTrimPostInput()
    {
        $name = 'max';
        $lastname = 'something';
        $another = 'sayhitoanothervariable';

        $_POST = [
            'name' => '           ' . $name . '             ',
            'story' => [
                'lastname' => '         ' . $lastname . '    '
            ],
            'multi' => [
                'nested' => [
                    'arrays' => [
                        'another' => '          ' . $another . '         '
                    ]
                ]
            ]
        ];
        $request = new Phalcon();
        $request->enableSanitize();

        $data = $request->getPostData();

        $this->assertTrue(strlen($data['name']) === strlen($name));
        $this->assertTrue(strlen($data['story']['lastname']) === strlen($lastname));
        $this->assertTrue(strlen($data['multi']['nested']['arrays']['another']) === strlen($another));
    }

    public function testTrimPutInput()
    {
        //following PHALCON request test https://github.com/phalcon/cphalcon/blob/4.0.x/tests/unit/Http/Request/

        $name = 'max';
        $lastname = 'something';
        $another = 'sayhitoanothervariable';

        // Valid
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', PhpStream::class);

        $input = [
            'name' => '           ' . $name . '             ',
            'story' => [
                'lastname' => '         ' . $lastname . '    '
            ],
            'multi' => [
                'nested' => [
                    'arrays' => [
                        'another' => '          ' . $another . '         '
                    ]
                ]
            ]
        ];

        file_put_contents(
            'php://input',
            json_encode($input)
        );

        $_SERVER = [
            'REQUEST_METHOD' => 'PUT',
            'CONTENT_TYPE' => 'application/json',
        ];

        $request = new Phalcon();
        $request->enableSanitize();

        $data = $request->getPutData();

        $this->assertTrue(strlen($data['name']) === strlen($name));
        $this->assertTrue(strlen($data['story']['lastname']) === strlen($lastname));
        $this->assertTrue(strlen($data['multi']['nested']['arrays']['another']) === strlen($another));

        stream_wrapper_restore('php');
    }

    public function testTrimRawPutInput()
    {
        $name = 'max';
        $lastname = 'something';
        $another = 'sayhitoanothervariable';

        // Empty
        $request = new Phalcon();
        $this->assertEmpty($request->getRawBody());

        // Valid
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', PhpStream::class);

        $input = [
            'name' => '           ' . $name . '             ',
            'story' => [
                'lastname' => '         ' . $lastname . '    '
            ],
            'multi' => [
                'nested' => [
                    'arrays' => [
                        'another' => '          ' . $another . '         '
                    ]
                ]
            ]
        ];

        file_put_contents('php://input', json_encode($input));

        $request = new Phalcon();

        $data = filter_var($request->getJsonRawBody(true), FILTER_CALLBACK, ['options' => 'trim']);

        $this->assertTrue(strlen($data['name']) === strlen($name));
        $this->assertTrue(strlen($data['story']['lastname']) === strlen($lastname));
        $this->assertTrue(strlen($data['multi']['nested']['arrays']['another']) === strlen($another));

        stream_wrapper_restore('php');
    }
}
