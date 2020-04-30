<?php

declare(strict_types=1);

use Baka\Support\Date;
use Baka\Support\Str;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    public function testMinsAgo()
    {
        $timeAgo = Date::howLongAgo(date('Y-m-d H:i:s', strtotime('-10 minutes')));

        $this->assertTrue(
            Str::contains($timeAgo, 'mins. ago')
        );
    }

    public function testHoursAgo()
    {
        $timeAgo = Date::howLongAgo(date('Y-m-d H:i:s', strtotime('-8 hours')));

        $this->assertTrue(
            Str::contains($timeAgo, 'hrs. ago')
        );
    }


    public function testDaysAgo()
    {
        $timeAgo = Date::howLongAgo(date('Y-m-d H:i:s', strtotime('-28 hours')));

        $this->assertTrue(
            Str::contains($timeAgo, 'day')
        );
    }

    public function testDatesAgo()
    {
        $timeAgo = Date::howLongAgo(date('Y-m-d H:i:s', strtotime('-750 hours')));

        $this->assertTrue(
            Str::contains($timeAgo, date('Y'))
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
