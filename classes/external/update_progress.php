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
 * update_progress.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosequence\external;

use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use mod_videosequence\progress_manager;

/**
 * AJAX endpoint that stores video progress.
 */
class update_progress extends external_api {
    /**
     * Method execute_parameters.
     *
     * @return external_function_parameters Return value.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'current' => new external_value(PARAM_FLOAT, 'Current position'),
            'duration' => new external_value(PARAM_FLOAT, 'Duration'),
            'segmentstart' => new external_value(PARAM_FLOAT, 'Segment start'),
            'segmentend' => new external_value(PARAM_FLOAT, 'Segment end'),
            'elapsed' => new external_value(PARAM_FLOAT, 'Elapsed wall time'),
        ]);
    }

    /**
     * Method execute.
     *
     * @param int $cmid Parameter cmid.
     * @param float $current Parameter current.
     * @param float $duration Parameter duration.
     * @param float $segmentstart Parameter segmentstart.
     * @param float $segmentend Parameter segmentend.
     * @param float $elapsed Parameter elapsed.
     * @return array Return value.
     */
    public static function execute(int $cmid, float $current, float $duration, float $segmentstart,
                                   float $segmentend, float $elapsed): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(),
            compact('cmid', 'current', 'duration', 'segmentstart', 'segmentend', 'elapsed'));
        $cm = get_coursemodule_from_id('videosequence', $params['cmid'], 0, false, MUST_EXIST);
        $course = get_course($cm->course);
        require_login($course, true, $cm);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videosequence:view', $context);
        $activity = $DB->get_record('videosequence', ['id' => $cm->instance], '*', MUST_EXIST);
        $manager = new progress_manager();
        $progress = $manager->get_or_create($activity->id, $USER->id);
        $progress = $manager->update($activity, $progress, $params['current'], $params['duration'],
            $params['segmentstart'], $params['segmentend'], $params['elapsed']);
        $completion = new \completion_info($course);
        if ($completion->is_enabled($cm)) {
            $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);
        }
        return [
            'percent' => (float)$progress->percent,
            'lastposition' => (float)$progress->lastposition,
            'unlocked' => (float)$progress->percent >= (float)$activity->minwatchpercent,
        ];
    }

    /**
     * Method execute_returns.
     *
     * @return external_single_structure Return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'percent' => new external_value(PARAM_FLOAT, 'Watched percent'),
            'lastposition' => new external_value(PARAM_FLOAT, 'Last position'),
            'unlocked' => new external_value(PARAM_BOOL, 'Sequence is unlocked'),
        ]);
    }
}
