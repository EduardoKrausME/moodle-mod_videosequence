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
 * submit_attempt.php
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
use core_external\external_multiple_structure;
use core_external\external_value;
use mod_videosequence\attempt_manager;

/**
 * AJAX endpoint for sequence submissions.
 */
class submit_attempt extends external_api {
    /**
     * Method execute_parameters.
     *
     * @return external_function_parameters Return value.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'answerjson' => new external_value(PARAM_RAW, 'JSON encoded answer'),
        ]);
    }

    /**
     * Method execute.
     *
     * @param int $cmid Parameter cmid.
     * @param string $answerjson Parameter answerjson.
     * @return array Return value.
     */
    public static function execute(int $cmid, string $answerjson): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'answerjson'));
        $cm = get_coursemodule_from_id('videosequence', $params['cmid'], 0, false, MUST_EXIST);
        $course = get_course($cm->course);
        require_login($course, true, $cm);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videosequence:view', $context);
        $answer = json_decode($params['answerjson'], true);
        if (!is_array($answer)) {
            throw new \moodle_exception('invalidanswer', 'videosequence');
        }
        $activity = $DB->get_record('videosequence', ['id' => $cm->instance], '*', MUST_EXIST);
        $result = (new attempt_manager())->submit($activity, $USER->id, array_values($answer));
        $completion = new \completion_info($course);
        if ($completion->is_enabled($cm)) {
            $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);
        }
        return $result;
    }

    /**
     * Method execute_returns.
     *
     * @return external_single_structure Return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'attempt' => new external_value(PARAM_INT, 'Attempt number'),
            'correct' => new external_value(PARAM_INT, 'Correct positions, -1 while hidden'),
            'total' => new external_value(PARAM_INT, 'Total steps'),
            'score' => new external_value(PARAM_FLOAT, 'Percent score, -1 while hidden'),
            'grade' => new external_value(PARAM_FLOAT, 'Grade, -1 while hidden'),
            'feedbackavailable' => new external_value(PARAM_BOOL, 'Whether feedback is shown'),
            'positionresults' => new external_multiple_structure(
                new external_value(PARAM_BOOL, 'Whether this position is correct')),
            'canretry' => new external_value(PARAM_BOOL, 'Whether another attempt is available'),
        ]);
    }
}
