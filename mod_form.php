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
 * mod_form.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Main activity form.
 */
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Class mod_videosequence_mod_form.
 */
class mod_videosequence_mod_form extends moodleform_mod {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('videosequencename', 'videosequence'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $this->standard_intro_elements();

        $mform->addElement('html', '<h3>' . get_string('videoheader', 'videosequence') . '</h3>');
        $mform->addElement('select', 'videosource', get_string('videosource', 'videosequence'), [
            'upload' => get_string('sourceupload', 'videosequence'),
            'url' => get_string('sourceurl', 'videosequence'),
            'youtube' => get_string('sourceyoutube', 'videosequence'),
            'vimeo' => get_string('sourcevimeo', 'videosequence'),
        ]);
        $mform->setDefault('videosource', 'upload');
        $mform->setType('videosource', PARAM_ALPHA);
        $mform->addElement('filemanager', 'videofile', get_string('videofile', 'videosequence'), null, [
            'subdirs' => 0, 'accepted_types' => ['video'],
        ]);
        $mform->hideIf('videofile', 'videosource', 'neq', 'upload');
        $mform->addElement('url', 'videourl', get_string('videourl', 'videosequence'), ['size' => 80]);
        $mform->setType('videourl', PARAM_URL);
        $mform->hideIf('videourl', 'videosource', 'eq', 'upload');
        $mform->addElement('filemanager', 'poster', get_string('poster', 'videosequence'), null, [
            'subdirs' => 0, 'accepted_types' => ['image'],
        ]);
        $mform->addElement('selectyesno', 'resumeplayback', get_string('resumeplayback', 'videosequence'));
        $mform->setDefault('resumeplayback', 1);
        $mform->addElement('selectyesno', 'allowseek', get_string('allowseek', 'videosequence'));
        $mform->setDefault('allowseek', 1);

        $mform->addElement('html', '<h3>' . get_string('sequenceheader', 'videosequence') . '</h3>');
        $mform->addElement('select', 'sequencemode', get_string('sequencemode', 'videosequence'), [
            0 => get_string('modereorder', 'videosequence'),
            1 => get_string('modecreate', 'videosequence'),
        ]);
        $mform->addHelpButton('sequencemode', 'sequencemode', 'videosequence');
        $mform->addElement('text', 'minwatchpercent', get_string('minwatchpercent', 'videosequence'), ['size' => 6]);
        $mform->setType('minwatchpercent', PARAM_INT);
        $mform->setDefault('minwatchpercent', 80);
        $mform->addRule('minwatchpercent', null, 'numeric', null, 'client');
        $mform->addElement('text', 'maxattempts', get_string('maxattempts', 'videosequence'), ['size' => 6]);
        $mform->setType('maxattempts', PARAM_INT);
        $mform->setDefault('maxattempts', 3);
        $mform->addHelpButton('maxattempts', 'maxattempts', 'videosequence');
        $mform->addElement('select', 'feedbackmode', get_string('feedbackmode', 'videosequence'), [
            1 => get_string('feedbackimmediate', 'videosequence'),
            0 => get_string('feedbackfinal', 'videosequence'),
        ]);
        $mform->addElement('text', 'grade', get_string('grade', 'grades'), ['size' => 6]);
        $mform->setType('grade', PARAM_FLOAT);
        $mform->setDefault('grade', 100);
        $mform->addRule('grade', null, 'numeric', null, 'client');

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Method data_preprocessing.
     *
     * @param mixed $defaultvalues Parameter defaultvalues.
     * @return void Return value.
     */
    public function data_preprocessing(&$defaultvalues): void {
        if (!$this->current || empty($this->current->coursemodule)) {
            return;
        }
        $context = context_module::instance($this->current->coursemodule);
        $draftid = file_get_submitted_draft_itemid('videofile');
        file_prepare_draft_area($draftid, $context->id, 'mod_videosequence', 'video', 0, ['subdirs' => 0, 'maxfiles' => 1]);
        $defaultvalues['videofile'] = $draftid;
        $posterid = file_get_submitted_draft_itemid('poster');
        file_prepare_draft_area($posterid, $context->id, 'mod_videosequence', 'poster', 0, ['subdirs' => 0, 'maxfiles' => 1]);
        $defaultvalues['poster'] = $posterid;
    }

    /**
     * Method validation.
     *
     * @param mixed $data Parameter data.
     * @param mixed $files Parameter files.
     * @return array Return value.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if ((int)$data['minwatchpercent'] < 0 || (int)$data['minwatchpercent'] > 100) {
            $errors['minwatchpercent'] = get_string('errorpercent', 'videosequence');
        }
        if ((int)$data['maxattempts'] < 0) {
            $errors['maxattempts'] = get_string('errornonnegative', 'videosequence');
        }
        if ((float)$data['grade'] < 0) {
            $errors['grade'] = get_string('errornonnegative', 'videosequence');
        }
        if (($data['videosource'] ?? '') !== 'upload' && empty($data['videourl'])) {
            $errors['videourl'] = get_string('errorvideourl', 'videosequence');
        }
        foreach (['videofile', 'poster'] as $field) {
            $draftid = (int)($data[$field] ?? 0);
            if ($draftid > 0) {
                $draftinfo = file_get_draft_area_info($draftid);
                if ((int)$draftinfo['filecount'] > 1) {
                    $errors[$field] = get_string('errormaxfiles', 'videosequence');
                }
            }
        }
        return $errors;
    }

    /**
     * Method add_completion_rules.
     *
     * @return array Return value.
     */
    public function add_completion_rules(): array {
        $mform = $this->_form;
        $mform->addElement('checkbox', 'completionwatch', '', get_string('completionwatch', 'videosequence'));
        $mform->setDefault('completionwatch', 1);
        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'videosequence'));
        $mform->setDefault('completionsubmit', 1);
        return ['completionwatch', 'completionsubmit'];
    }

    /**
     * Method completion_rule_enabled.
     *
     * @param mixed $data Parameter data.
     * @return bool Return value.
     */
    public function completion_rule_enabled($data): bool {
        return !empty($data['completionwatch']) || !empty($data['completionsubmit']);
    }
}
