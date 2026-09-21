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
 * step_form.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosequence\form;

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/formslib.php");

/**
 * Form used to edit a sequence step.
 */
class step_form extends \moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'stepid');
        $mform->setType('stepid', PARAM_INT);
        $mform->addElement('text', 'title', get_string('steptitle', 'videosequence'), ['size' => 70]);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addElement('textarea', 'description', get_string('stepdescription', 'videosequence'), ['rows' => 4, 'cols' => 70]);
        $mform->setType('description', PARAM_TEXT);
        $mform->addElement('textarea', 'aliases', get_string('stepaliases', 'videosequence'), ['rows' => 3, 'cols' => 70]);
        $mform->setType('aliases', PARAM_TEXT);
        $mform->addHelpButton('aliases', 'stepaliases', 'videosequence');
        $mform->addElement('text', 'timestart', get_string('timestart', 'videosequence'), ['size' => 12]);
        $mform->setType('timestart', PARAM_FLOAT);
        $mform->setDefault('timestart', 0);
        $mform->addElement('text', 'timeend', get_string('timeend', 'videosequence'), ['size' => 12]);
        $mform->setType('timeend', PARAM_FLOAT);
        $mform->setDefault('timeend', 0);
        $this->add_action_buttons(true, get_string('savestep', 'videosequence'));
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
        if ((float)$data['timestart'] < 0 || (float)$data['timeend'] < 0) {
            $errors['timestart'] = get_string('errornonnegative', 'videosequence');
        }
        if ((float)$data['timeend'] > 0 && (float)$data['timeend'] < (float)$data['timestart']) {
            $errors['timeend'] = get_string('errortimeend', 'videosequence');
        }
        return $errors;
    }
}
