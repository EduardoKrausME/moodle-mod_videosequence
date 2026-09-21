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
 * progress_manager.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosequence;

use stdClass;

/**
 * Stores watched progress.
 */
class progress_manager {
    /**
     * Method get_or_create.
     *
     * @param int $activityid Parameter activityid.
     * @param int $userid Parameter userid.
     * @return stdClass Return value.
     */
    public function get_or_create(int $activityid, int $userid): stdClass {
        global $DB;
        $record = $DB->get_record('videosequence_progress', ['videosequenceid' => $activityid, 'userid' => $userid]);
        if ($record) {
            return $record;
        }
        $now = time();
        $record = (object)[
            'videosequenceid' => $activityid, 'userid' => $userid, 'duration' => 0, 'lastposition' => 0,
            'watchedsegments' => '[]', 'uniquewatched' => 0, 'totalwatchtime' => 0, 'percent' => 0,
            'timecreated' => $now, 'timemodified' => $now,
        ];
        $record->id = $DB->insert_record('videosequence_progress', $record);
        return $record;
    }

    /**
     * Method update.
     *
     * @param stdClass $activity Parameter activity.
     * @param stdClass $progress Parameter progress.
     * @param float $current Parameter current.
     * @param float $duration Parameter duration.
     * @param float $segmentstart Parameter segmentstart.
     * @param float $segmentend Parameter segmentend.
     * @param float $elapsed Parameter elapsed.
     * @return stdClass Return value.
     */
    public function update(stdClass $activity, stdClass $progress, float $current, float $duration,
                           float    $segmentstart, float $segmentend, float $elapsed): stdClass {
        global $DB;
        $duration = max(0.0, $duration);
        $current = max(0.0, min($duration, $current));
        $segmentstart = max(0.0, min($duration, $segmentstart));
        $segmentend = max($segmentstart, min($duration, $segmentend));
        $serverelapsed = max(1.0, min(30.0, (float)(time() - (int)$progress->timemodified) + 2.0));
        $elapsed = max(0.0, min($serverelapsed, min(30.0, $elapsed)));
        $maxcontent = $elapsed * 2.0 + 4.0;
        if ($segmentend - $segmentstart > $maxcontent) {
            $segmentend = $segmentstart + $maxcontent;
        }
        $segments = segment_manager::decode($progress->watchedsegments);
        $furthest = segment_manager::furthest($segments);
        if (!$activity->allowseek && $segmentstart > $furthest + 5.0) {
            $segmentstart = $furthest;
            $segmentend = min($duration, $furthest + $maxcontent);
            $current = min($current, $segmentend);
        }
        $segments = segment_manager::merge($segments, [$segmentstart, $segmentend], $duration);
        $progress->duration = max((float)$progress->duration, $duration);
        $progress->lastposition = $current;
        $progress->watchedsegments = segment_manager::encode($segments);
        $progress->uniquewatched = segment_manager::unique_seconds($segments);
        $progress->totalwatchtime = round(
            (float)$progress->totalwatchtime + min($elapsed, max(0.0, $segmentend - $segmentstart)),
            3);
        $progress->percent = $progress->duration > 0 ?
            round(min(100, ($progress->uniquewatched / $progress->duration) * 100), 2) : 0;
        $progress->timemodified = time();
        $DB->update_record('videosequence_progress', $progress);
        return $progress;
    }
}
