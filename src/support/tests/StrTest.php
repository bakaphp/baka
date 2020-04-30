<?php

declare(strict_types=1);

use Baka\Support\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
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
}
