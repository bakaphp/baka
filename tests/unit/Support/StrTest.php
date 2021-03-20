<?php

declare(strict_types=1);

namespace Baka\Test\Unit\Support;

use Baka\Support\Str;
use PhalconUnitTestCase;

class StrTest extends PhalconUnitTestCase
{
    public function testEndsWith()
    {
        $this->assertTrue(
            Str::endsWith('Hi, this is me', 'me')
        );
    }

    public function testStartsWith()
    {
        $this->assertTrue(
            Str::startsWith('Hi, this is me', 'Hi')
        );
    }

    public function testIncludes()
    {
        $this->assertTrue(
            Str::includes('example', 'This is an example string')
        );
    }

    public function testContains()
    {
        $this->assertTrue(
            Str::contains('This is an example string', 'example')
        );
    }

    public function testIsUpperCase()
    {
        $this->assertTrue(
            Str::isUpperCase('MORNING SHOWS THE DAY!')
        );
    }

    public function testIsLowerCase()
    {
        $this->assertTrue(
            Str::isLowerCase('morning shows the day!')
        );
    }

    public function testIsAnagram()
    {
        $this->assertTrue(
            Str::isAnagram('act', 'cat')
        );
    }

    public function testPalindrome()
    {
        $this->assertTrue(
            Str::palindrome('racecar')
        );
    }

    public function testFirstStringBetween()
    {
        $this->assertSame(
            'custom',
            Str::firstStringBetween('This is a [custom] string', '[', ']')
        );

        $this->assertSame(
            '',
            Str::firstStringBetween('', '[', ']')
        );

        $this->assertSame(
            '',
            Str::firstStringBetween('This is a [custom] string', '[', '#')
        );
    }

    public function testCountVowels()
    {
        $this->assertSame(4, Str::countVowels('sampleInput'));

        $this->assertSame(0, Str::countVowels(''));
    }

    public function testDecapitalize()
    {
        $this->assertSame('fooBar', Str::decapitalize('FooBar'));

        $this->assertSame('fOOBAR', Str::decapitalize('FooBar', true));
    }

    public function testCamelize()
    {
        $this->assertSame('FooBar', Str::camelize('foo_bar'));
        $this->assertSame('FooBar', Str::camelize('foo_bAr'));
        $this->assertSame('FooBar', Str::camelize('Foo*BaR', '*'));
    }

    public function testLetterPlusNumber()
    {
        $this->assertSame('D', Str::letterPlusNumber('A', 3));
        $this->assertSame('D', Str::letterPlusNumber('D', 0));
    }

    public function testJsonToArray()
    {
        $json = '[{"id":"57","name":"Clientes","description":"e","scope":0,"companies_id":1,"apps_id":15,"created_at":"2019-06-11 20:34:05","updated_at":"2021-01-24 01:44:03","is_default":1,"is_active":1,"is_deleted":0}]';
        $jsonTwo = '{"settingsmenu":{"app-settings":0,"companies-manager":0,"companies-settings":0}}';
        $array = Str::jsonToArray($json);
        $arrayTwo = Str::jsonToArray($jsonTwo);

        $this->assertIsArray($array);
        $this->assertIsArray($arrayTwo);
    }

    public function testEmptyJsonNotArray()
    {
        $json = null;
        $array = Str::jsonToArray($json);

        $this->assertIsNotArray($array);
    }
}
