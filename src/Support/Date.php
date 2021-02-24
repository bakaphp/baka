<?php

declare(strict_types=1);

namespace Baka\Support;

use DateTime;
use Exception;

class Date
{
    /**
     * Calculate how long ago was this time compare to now.
     *
     * Example: x mins from last update
     *
     * @param  string  $time
     * @param  bool $timestamp
     *
     * @return string
     */
    public static function howLongAgo(string $date) : string
    {
        if (!self::validate($date)) {
            throw new Exception('Params must be a valid date Y-m-d H:i:s format');
        }

        $ts = strtotime($date);

        $now = time();
        $diff = $now - $ts;

        $minute = 60;
        $hour = $minute * 60;
        $day = $hour * 24;
        $month = $day * 30;
        $years = $day * 360;

        if ($diff < $minute) {
            $text = _('< 1 min.');

            return sprintf('%ds', $diff);
        } elseif ($diff < $hour) {
            $mins = (int) floor($diff / $minute);

            return sprintf('%dm', $mins);
        } elseif ($diff < $day) {
            $hrs = (int) floor($diff / $hour);
            $mins = (int)  floor(($diff - $hrs * $hour) / $minute);
            $text = sprintf('%dh', $hrs);

            return $text;
        } elseif ($diff < $month) {
            $days = (int) floor($diff / $day);
            $text = sprintf('%dd', $days);

            return $text;
        } elseif ($diff < $years) {
            return date('M d', $ts);
        } else {
            return date('M d, Y', $ts);
        }
    }

    /**
     * Validate a date given a format.
     *
     * @param string $date
     * @param string $format
     *
     * @return bool
     */
    public static function validate(string $date, string $format = 'Y-m-d H:i:s') : bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
