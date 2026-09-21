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
 * custom_completion.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosequence\completion;

use core_completion\activity_custom_completion;

/**
 * Custom completion rules.
 */
class custom_completion extends activity_custom_completion {
    /**
     * Method get_state.
     *
     * @param string $rule Parameter rule.
     * @return int Return value.
     */
    public function get_state(string $rule): int {
        global $DB;
        $activity = $DB->get_record('videosequence', ['id' => $this->cm->instance], '*', MUST_EXIST);
        if ($rule === 'completionwatch') {
            $progress = $DB->get_record('videosequence_progress', ['videosequenceid' => $activity->id, 'userid' => $this->userid]);
            return $progress && (float)$progress->percent >= (float)$activity->minwatchpercent ?
                COMPLETION_COMPLETE :
                COMPLETION_INCOMPLETE;
        }
        if ($rule === 'completionsubmit') {
            return $DB->record_exists('videosequence_attempts', ['videosequenceid' => $activity->id, 'userid' => $this->userid])
                ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }
        return COMPLETION_INCOMPLETE;
    }

    /**
     * Method get_defined_custom_rules.
     *
     * @return array Return value.
     */
    public static function get_defined_custom_rules(): array {
        return ['completionwatch', 'completionsubmit'];
    }

    /**
     * Method get_custom_rule_descriptions.
     *
     * @return array Return value.
     */
    public function get_custom_rule_descriptions(): array {
        global $DB;
        $activity = $DB->get_record('videosequence', ['id' => $this->cm->instance], '*', MUST_EXIST);
        return [
            'completionwatch' => get_string('completiondetail:watch', 'videosequence', $activity->minwatchpercent),
            'completionsubmit' => get_string('completiondetail:submit', 'videosequence'),
        ];
    }

    /**
     * Method get_sort_order.
     *
     * @return array Return value.
     */
    public function get_sort_order(): array {
        return ['completionwatch' => 1, 'completionsubmit' => 2];
    }
}
