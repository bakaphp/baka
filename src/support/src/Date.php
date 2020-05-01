<?php

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
     * @param  boolean $timestamp
     * @return string
     */
    public static function howLongAgo(string $date): string
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

        if ($diff < $minute) {
            $text = _('< 1 min.');

            return sprintf(_('%s ago'), $text);
        } elseif ($diff < $hour) {
            $mins = floor($diff / $minute);

            return sprintf(ngettext('%d min. ago', '%d mins. ago', $mins), $mins);
        } elseif ($diff < $day) {
            $hrs = floor($diff / $hour);
            $mins = floor(($diff - $hrs * $hour) / $minute);

            $text = sprintf(ngettext('%d hr.', '%d hrs.', $hrs), $hrs);

            if ($mins > 0) {
                $text .= ' ' . sprintf(
                    ngettext('%d min.', '%d mins.', $mins),
                    $mins
                );
            }

            return sprintf(_('%s ago'), $text);
        } elseif ($diff < $month) {
            $days = floor($diff / $day);

            $text = sprintf(ngettext('%d day', '%d days', $days), $days);

            if ($days < 2) {
                $hrs = floor(($diff - $days * $day) / $hour);
                $text .= ' ' . sprintf(ngettext('%d hr.', '%d hrs.', $hrs), $hrs);
            }

            return sprintf(_('%s ago'), $text);
        } else {
            return date('Y-m-d', $ts);
        }
    }

    /**
     * Validate a date given a format
     *
     * @param string $date
     * @param string $format
     * @return bool
     */
    public static function validate(string $date, string $format = 'Y-m-d H:i:s'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
