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
 * backup_videosequence_stepslib.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Backup structure step.
 */
class backup_videosequence_activity_structure_step extends backup_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');
        $activity = new backup_nested_element('videosequence', ['id'], [
            'course', 'name', 'intro', 'introformat', 'videosource', 'videourl', 'resumeplayback', 'allowseek', 'sequencemode',
            'minwatchpercent', 'maxattempts', 'feedbackmode', 'grade', 'completionwatch',
            'completionsubmit', 'timecreated', 'timemodified',
        ]);
        $steps = new backup_nested_element('steps');
        $step = new backup_nested_element('step', ['id'],
            ['position', 'title', 'description', 'aliases', 'timestart', 'timeend', 'timecreated', 'timemodified']);
        $progresses = new backup_nested_element('progresses');
        $progress = new backup_nested_element('progress', ['id'],
            [
                'userid', 'duration', 'lastposition', 'watchedsegments', 'uniquewatched',
                'totalwatchtime', 'percent', 'timecreated', 'timemodified',
            ]);
        $attempts = new backup_nested_element('attempts');
        $attempt = new backup_nested_element('attempt', ['id'],
            ['userid', 'attemptno', 'answerjson', 'correctcount', 'totalsteps', 'score', 'grade', 'timecreated', 'timemodified']);
        $activity->add_child($steps);
        $steps->add_child($step);
        $activity->add_child($progresses);
        $progresses->add_child($progress);
        $activity->add_child($attempts);
        $attempts->add_child($attempt);
        $activity->set_source_table('videosequence', ['id' => backup::VAR_ACTIVITYID]);
        $step->set_source_table('videosequence_steps', ['videosequenceid' => backup::VAR_PARENTID], 'position ASC');
        if ($userinfo) {
            $progress->set_source_table('videosequence_progress', ['videosequenceid' => backup::VAR_PARENTID]);
            $attempt->set_source_table('videosequence_attempts', ['videosequenceid' => backup::VAR_PARENTID], 'attemptno ASC');
        }
        $progress->annotate_ids('user', 'userid');
        $attempt->annotate_ids('user', 'userid');
        $activity->annotate_files('mod_videosequence', 'intro', null);
        $activity->annotate_files('mod_videosequence', 'video', null);
        $activity->annotate_files('mod_videosequence', 'poster', null);
        return $this->prepare_activity_structure($activity);
    }
}
