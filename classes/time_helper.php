<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * time_helper.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosequence;
/**
 * Formatting helper for video timestamps.
 */
class time_helper {
    /**
     * Method format.
     *
     * @param float $seconds Parameter seconds.
     * @return string Return value.
     */
    public static function format(float $seconds): string {
        $seconds = max(0, (int)round($seconds));
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;
        return $hours > 0 ? sprintf('%d:%02d:%02d', $hours, $minutes, $secs) : sprintf('%02d:%02d', $minutes, $secs);
    }

    /**
     * Method range.
     *
     * @param float $start Parameter start.
     * @param float $end Parameter end.
     * @return string Return value.
     */
    public static function range(float $start, float $end): string {
        if ($start <= 0 && $end <= 0) {
            return '-';
        }
        if ($end > $start) {
            return self::format($start) . '–' . self::format($end);
        }
        return self::format($start);
    }
}
