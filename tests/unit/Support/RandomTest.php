<?php

declare(strict_types=1);

namespace Baka\Test\Unit\Support;

use Baka\Support\Random;
use PhalconUnitTestCase;

class RandomTest extends PhalconUnitTestCase
{
    public function testEmailNotAlphanumeric()
    {
        $email = $this->faker->email;

        $this->assertFalse(
            ctype_alnum($email)
        );
    }

    public function testDisplaynameFromEmail()
    {
        $email = $this->faker->email;
        $displayname = Random::generateDisplayName($email);

        $this->assertTrue(
            ctype_alnum($displayname)
        );

        $this->assertTrue(
            strlen($displayname) <= 45
        );
    }

    public function testDisplaynameFromEmailAgain()
    {
        $email = $this->faker->email;
        $displayname = Random::generateDisplayNameFromEmail($email);

        $this->assertTrue(
            ctype_alnum($displayname)
        );
        $this->assertTrue(
            strlen($displayname) <= 45
        );
    }

    public function testDisplaynameFromFirstAndLastname()
    {
        $name = $this->faker->firstname . ' ' . $this->faker->lastname;
        $displayname = Random::generateDisplayNameFromEmail($name);

        $this->assertTrue(
            ctype_alnum($displayname)
        );
        $this->assertTrue(
            strlen($displayname) <= 45
        );
    }

    public function testDisplaynameFromName()
    {
        $name = $this->faker->name;
        $displayname = Random::generateDisplayNameFromEmail($name);

        $this->assertTrue(
            ctype_alnum($displayname)
        );
        $this->assertTrue(
            strlen($displayname) <= 45
        );
    }
}
