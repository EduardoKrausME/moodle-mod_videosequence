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
 * restore_videosequence_stepslib.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore structure step.
 */
class restore_videosequence_activity_structure_step extends restore_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $paths = [new restore_path_element('videosequence', '/activity/videosequence')];
        $paths[] = new restore_path_element('videosequence_step', '/activity/videosequence/steps/step');
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('videosequence_progress', '/activity/videosequence/progresses/progress');
            $paths[] = new restore_path_element('videosequence_attempt', '/activity/videosequence/attempts/attempt');
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Method process_videosequence.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videosequence($data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = time();
        $newid = $DB->insert_record('videosequence', $data);
        $this->apply_activity_instance($newid);
        $this->set_mapping('videosequence', $oldid, $newid, true);
    }

    /**
     * Method process_videosequence_step.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videosequence_step($data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->videosequenceid = $this->get_new_parentid('videosequence');
        $newid = $DB->insert_record('videosequence_steps', $data);
        $this->set_mapping('videosequence_step', $oldid, $newid);
    }

    /**
     * Method process_videosequence_progress.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videosequence_progress($data): void {
        global $DB;
        $data = (object)$data;
        $data->videosequenceid = $this->get_new_parentid('videosequence');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $DB->insert_record('videosequence_progress', $data);
    }

    /**
     * Method process_videosequence_attempt.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videosequence_attempt($data): void {
        global $DB;
        $data = (object)$data;
        $data->videosequenceid = $this->get_new_parentid('videosequence');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $answer = json_decode($data->answerjson, true);
        if (is_array($answer)) {
            foreach ($answer as &$value) {
                if (is_numeric($value)) {
                    $value = $this->get_mappingid('videosequence_step', (int)$value, (int)$value);
                }
            }
            unset($value);
            $data->answerjson = json_encode($answer, JSON_UNESCAPED_UNICODE);
        }
        $DB->insert_record('videosequence_attempts', $data);
    }

    /**
     * Method after_execute.
     *
     * @return void Return value.
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_videosequence', 'intro', null);
        $this->add_related_files('mod_videosequence', 'video', null);
        $this->add_related_files('mod_videosequence', 'poster', null);
    }
}
