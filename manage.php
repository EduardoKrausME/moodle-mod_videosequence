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
 * manage.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videosequence\time_helper;

require_once('../../config.php');
$id = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$stepid = optional_param('stepid', 0, PARAM_INT);
$cm = get_coursemodule_from_id('videosequence', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videosequence', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videosequence:manage', $context);
$PAGE->set_url('/mod/videosequence/manage.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('managesequence', 'videosequence'));
$PAGE->set_heading(format_string($course->fullname));
if (in_array($action, ['up', 'down', 'delete'], true) && $stepid) {
    require_sesskey();
    $step = $DB->get_record('videosequence_steps', ['id' => $stepid, 'videosequenceid' => $activity->id], '*', MUST_EXIST);
    if ($action === 'delete') {
        $DB->delete_records('videosequence_steps', ['id' => $step->id]);
    } else {
        $operator = $action === 'up' ? '<' : '>';
        $order = $action === 'up' ? 'position DESC' : 'position ASC';
        $others = $DB->get_records_select(
            'videosequence_steps',
            'videosequenceid = :activityid AND position ' . $operator . ' :position',
            ['activityid' => $activity->id, 'position' => $step->position],
            $order,
            '*',
            0,
            1
        );
        $other = $others ? reset($others) : false;
        if ($other) {
            $transaction = $DB->start_delegated_transaction();
            $old = $step->position;
            $target = $other->position;
            $DB->set_field('videosequence_steps', 'position', -1 * (int)$step->id, ['id' => $step->id]);
            $DB->set_field('videosequence_steps', 'position', $old, ['id' => $other->id]);
            $DB->set_field('videosequence_steps', 'position', $target, ['id' => $step->id]);
            $transaction->allow_commit();
        }
    }
    redirect(new moodle_url('/mod/videosequence/manage.php', ['id' => $cm->id]));
}
$steps = array_values($DB->get_records('videosequence_steps', ['videosequenceid' => $activity->id], 'position ASC'));
$rows = [];
foreach ($steps as $index => $step) {
    $rows[] = [
        'position' => $index + 1,
        'title' => format_string($step->title),
        'description' => s($step->description),
        'time' => time_helper::range((float)$step->timestart, (float)$step->timeend),
        'editurl' => (new moodle_url('/mod/videosequence/editstep.php',
            ['id' => $cm->id, 'stepid' => $step->id]))->out(false),
        'upurl' => (new moodle_url('/mod/videosequence/manage.php',
            ['id' => $cm->id, 'stepid' => $step->id, 'action' => 'up', 'sesskey' => sesskey()]))->out(false),
        'downurl' => (new moodle_url('/mod/videosequence/manage.php',
            ['id' => $cm->id, 'stepid' => $step->id, 'action' => 'down', 'sesskey' => sesskey()]))->out(false),
        'deleteurl' => (new moodle_url('/mod/videosequence/manage.php',
            ['id' => $cm->id, 'stepid' => $step->id, 'action' => 'delete', 'sesskey' => sesskey()]))->out(false),
        'first' => $index === 0, 'last' => $index === count($steps) - 1,
    ];
}
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_videosequence/manage', [
    'name' => format_string($activity->name), 'rows' => $rows, 'hasrows' => (bool)$rows,
    'addurl' => (new moodle_url('/mod/videosequence/editstep.php', ['id' => $cm->id]))->out(false),
    'viewurl' => (new moodle_url('/mod/videosequence/view.php', ['id' => $cm->id]))->out(false),
]);
echo $OUTPUT->footer();
