<?php

declare(strict_types=1);

namespace Baka\Test\Unit\Support;

use Baka\Support\Date;
use Baka\Support\Str;
use PhalconUnitTestCase;

class DateTest extends PhalconUnitTestCase
{
    public function testSecsAgo()
    {
        $timeAgo = Date::howLongAgo(date('Y-m-d H:i:s', strtotime('-35 seconds')));

        $this->assertTrue(
            Str::contains($timeAgo, 's')
        );
    }

    public function testMinsAgo()
    {
        $timeAgo = Date::howLongAgo(date('Y-m-d H:i:s', strtotime('-10 minutes')));

        $this->assertTrue(
            Str::contains($timeAgo, 'm')
        );
    }

    public function testHoursAgo()
    {
        $timeAgo = Date::howLongAgo(date('Y-m-d H:i:s', strtotime('-8 hours')));

        $this->assertTrue(
            Str::contains($timeAgo, 'h')
        );
    }

    public function testDaysAgo()
    {
        $timeAgo = Date::howLongAgo(date('Y-m-d H:i:s', strtotime('-68 hours')));

        $this->assertTrue(
            Str::contains($timeAgo, 'd')
        );
    }

    public function testMonthAgo()
    {
        $timeAgo = Date::howLongAgo(date('Y-m-d H:i:s', strtotime('-750 hours')));

        $this->assertTrue(
            Str::contains($timeAgo, date('M', strtotime('-750 hours')))
        );
    }

    public function testYearsAgo()
    {
        $timeAgo = Date::howLongAgo(date('Y-m-d H:i:s', strtotime('-1 year')));

        $this->assertTrue(
            Str::contains($timeAgo, date('Y', strtotime('-1 year')))
        );
    }

    public function testIncorrectDate()
    {
        $this->assertFalse(
            Date::validate('20222-01-01', 'Y-m-d')
        );
    }

    public function testCorrectDate()
    {
        $this->assertTrue(
            Date::validate('2020-01-01', 'Y-m-d')
        );
    }
}
