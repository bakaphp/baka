<?php

declare(strict_types=1);

namespace Baka\Test\Unit\Support;

use function Baka\basePath;
use function Baka\envValue;
use function Baka\getShortClassName;
use function Baka\isJson;
use Baka\Test\Support\Models\Leads;
use PhalconUnitTestCase;
use ReflectionClass;

class FunctionsTest extends PhalconUnitTestCase
{
    public function testIsNotAJson()
    {
        $this->assertFalse(isJson($this->faker->name));
    }

    public function testIsJson()
    {
        $json = '{
            "id": "1a8b8863-a859-4d68-b63a-c466e554fd13",
            "name": "Ada Lovelace",
            "email": "ada@geemail.com",
            "bio": "First programmer. No big deal.",
            "age": 198,
            "avatar": "http://en.wikipedia.org/wiki/File:Ada_lovelace.jpg"}';
        $this->assertTrue(isJson($json));
    }

    public function testGetBasePath()
    {
        $this->assertNotEmpty(basePath());
    }

    public function testEnvValue()
    {
        $this->assertTrue(envValue('TEST_TEST', 'helloworld') === 'helloworld');
    }

    public function testGetShortClassName()
    {
        $this->assertTrue(getShortClassName(new Leads()) == (new ReflectionClass(new Leads()))->getShortName());
    }
}
