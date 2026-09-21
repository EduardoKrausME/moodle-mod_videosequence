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
 * video_source.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosequence;
/**
 * Builds safe player URLs for supported sources.
 */
class video_source {
    /**
     * Method prepare.
     *
     * @param string $source Parameter source.
     * @param string $url Parameter url.
     * @return array Return value.
     */
    public static function prepare(string $source, string $url): array {
        $result = ['html5' => false, 'youtube' => false, 'vimeo' => false, 'embedurl' => $url];
        if ($source === 'upload' || $source === 'url') {
            $result['html5'] = true;
            return $result;
        }
        if ($source === 'youtube') {
            $id = self::youtube_id($url);
            $result['youtube'] = true;
            $result['embedurl'] = $id ? 'https://www.youtube-nocookie.com/embed/' .
                rawurlencode($id) . '?enablejsapi=1&rel=0' : $url;
            return $result;
        }
        if ($source === 'vimeo') {
            $id = self::vimeo_id($url);
            $result['vimeo'] = true;
            $result['embedurl'] = $id ? 'https://player.vimeo.com/video/' . rawurlencode($id) . '?api=1' : $url;
            return $result;
        }
        return $result;
    }

    /**
     * Method youtube_id.
     *
     * @param string $url Parameter url.
     * @return string Return value.
     */
    private static function youtube_id(string $url): string {
        if (preg_match(
            '~(?:youtu\\.be/|youtube(?:-nocookie)?\\.com/(?:watch\\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})~',
            $url, $match)) {
            return $match[1];
        }
        return preg_match('/^[A-Za-z0-9_-]{6,}$/', $url) ? $url : '';
    }

    /**
     * Method vimeo_id.
     *
     * @param string $url Parameter url.
     * @return string Return value.
     */
    private static function vimeo_id(string $url): string {
        if (preg_match('~vimeo\\.com/(?:video/)?([0-9]+)~', $url, $match)) {
            return $match[1];
        }
        return preg_match('/^[0-9]+$/', $url) ? $url : '';
    }
}
