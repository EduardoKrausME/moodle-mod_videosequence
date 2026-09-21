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
 * editstep.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videosequence\form\step_form;

require_once('../../config.php');
$id = required_param('id', PARAM_INT);
$stepid = optional_param('stepid', 0, PARAM_INT);
$cm = get_coursemodule_from_id('videosequence', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videosequence', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videosequence:manage', $context);
$PAGE->set_url('/mod/videosequence/editstep.php', ['id' => $cm->id, 'stepid' => $stepid]);
$PAGE->set_title(get_string($stepid ? 'editstep' : 'addstep', 'videosequence'));
$PAGE->set_heading(format_string($course->fullname));
$step = $stepid ? $DB->get_record('videosequence_steps',
    ['id' => $stepid, 'videosequenceid' => $activity->id], '*', MUST_EXIST) : null;
$form = new step_form(null, null, 'post', '', null, true, ['cmid' => $cm->id, 'stepid' => $stepid]);
if ($step) {
    $step->cmid = $cm->id;
    $step->stepid = $step->id;
    $form->set_data($step);
} else {
    $form->set_data((object)['cmid' => $cm->id, 'stepid' => 0]);
}
if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/videosequence/manage.php', ['id' => $cm->id]));
}
if ($data = $form->get_data()) {
    $now = time();
    if ($step) {
        $step->title = $data->title;
        $step->description = $data->description;
        $step->aliases = $data->aliases;
        $step->timestart = $data->timestart;
        $step->timeend = $data->timeend;
        $step->timemodified = $now;
        $DB->update_record('videosequence_steps', $step);
    } else {
        $position = (int)$DB->get_field_sql(
            'SELECT COALESCE(MAX(position), 0) FROM {videosequence_steps} WHERE videosequenceid = :id',
            ['id' => $activity->id]) + 1;
        $record = (object)[
            'videosequenceid' => $activity->id, 'position' => $position, 'title' => $data->title,
            'description' => $data->description, 'aliases' => $data->aliases, 'timestart' => $data->timestart,
            'timeend' => $data->timeend, 'timecreated' => $now, 'timemodified' => $now,
        ];
        $DB->insert_record('videosequence_steps', $record);
    }
    redirect(new moodle_url('/mod/videosequence/manage.php', ['id' => $cm->id]));
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($stepid ? 'editstep' : 'addstep', 'videosequence'));
$form->display();
echo $OUTPUT->footer();
