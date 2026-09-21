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
 * segment_manager.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosequence;
/**
 * Utilities for compact watched-segment tracking.
 */
class segment_manager {
    /**
     * Method decode.
     *
     * @param ?string $json Parameter json.
     * @return array Return value.
     */
    public static function decode(?string $json): array {
        if (!$json) {
            return [];
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }
        $out = [];
        foreach ($data as $pair) {
            if (is_array($pair) && count($pair) === 2 && is_numeric($pair[0]) && is_numeric($pair[1]) && $pair[1] > $pair[0]) {
                $out[] = [(float)$pair[0], (float)$pair[1]];
            }
        }
        return $out;
    }

    /**
     * Method encode.
     *
     * @param array $segments Parameter segments.
     * @return string Return value.
     */
    public static function encode(array $segments): string {
        return json_encode(array_values($segments));
    }

    /**
     * Method merge.
     *
     * @param array $segments Parameter segments.
     * @param array $newsegment Parameter newsegment.
     * @param float $duration Parameter duration.
     * @return array Return value.
     */
    public static function merge(array $segments, array $newsegment, float $duration): array {
        $start = max(0.0, min($duration, (float)$newsegment[0]));
        $end = max($start, min($duration, (float)$newsegment[1]));
        if ($end <= $start) {
            return $segments;
        }
        $segments[] = [$start, $end];
        usort($segments, static fn($a, $b) => $a[0] <=> $b[0]);
        $merged = [];
        foreach ($segments as $segment) {
            if (!$merged || $segment[0] > $merged[count($merged) - 1][1] + 0.75) {
                $merged[] = $segment;
            } else {
                $last = count($merged) - 1;
                $merged[$last][1] = max($merged[$last][1], $segment[1]);
            }
        }
        return $merged;
    }

    /**
     * Method unique_seconds.
     *
     * @param array $segments Parameter segments.
     * @return float Return value.
     */
    public static function unique_seconds(array $segments): float {
        $total = 0.0;
        foreach ($segments as $segment) {
            $total += max(0.0, $segment[1] - $segment[0]);
        }
        return round($total, 3);
    }

    /**
     * Method furthest.
     *
     * @param array $segments Parameter segments.
     * @return float Return value.
     */
    public static function furthest(array $segments): float {
        $furthest = 0.0;
        foreach ($segments as $segment) {
            $furthest = max($furthest, (float)$segment[1]);
        }
        return $furthest;
    }
}
