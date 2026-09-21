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
 * report.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('videosequence', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videosequence', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videosequence:viewreports', $context);
$PAGE->set_url('/mod/videosequence/report.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('report', 'videosequence'));
$PAGE->set_heading(format_string($course->fullname));
$users = get_enrolled_users($context, 'mod/videosequence:view', 0, 'u.id,u.firstname,u.lastname,u.email');
$rows = [];
foreach ($users as $user) {
    if (has_capability('mod/videosequence:manage', $context, $user->id)) {
        continue;
    }
    $progress = $DB->get_record('videosequence_progress',
        ['videosequenceid' => $activity->id, 'userid' => $user->id]);
    $attempts = $DB->get_records('videosequence_attempts',
        ['videosequenceid' => $activity->id, 'userid' => $user->id], 'attemptno DESC');
    $latest = $attempts ? reset($attempts) : null;
    $bestgrade = $attempts ? max(array_map(static fn($a) => (float)$a->grade, $attempts)) : null;
    $sequence = $latest ? json_decode($latest->answerjson, true) : [];
    if ($latest && (int)$activity->sequencemode === 0) {
        $ids = array_map('intval', is_array($sequence) ? $sequence : []);
        $titles = [];
        foreach ($ids as $stepid) {
            $title = $DB->get_field('videosequence_steps', 'title', ['id' => $stepid, 'videosequenceid' => $activity->id]);
            if ($title !== false) {
                $titles[] = $title;
            }
        }
        $sequence = $titles;
    }
    $complete = ((float)($progress->percent ?? 0) >= (float)$activity->minwatchpercent) && (bool)$latest;
    $rows[] = [
        'fullname' => fullname($user), 'email' => $user->email,
        'percent' => number_format((float)($progress->percent ?? 0), 1) . '%',
        'attempts' => count($attempts), 'sequence' => s(implode(' → ', is_array($sequence) ? $sequence : [])),
        'correct' => $latest ? $latest->correctcount . '/' . $latest->totalsteps : '-',
        'grade' => $bestgrade === null ? '-' : format_float($bestgrade, 2),
        'complete' => $complete, 'status' => get_string($complete ? 'completed' : 'incomplete', 'videosequence'),
    ];
}
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_videosequence/report', [
    'name' => format_string($activity->name), 'rows' => $rows, 'hasrows' => (bool)$rows,
    'viewurl' => (new moodle_url('/mod/videosequence/view.php', ['id' => $cm->id]))->out(false),
]);
echo $OUTPUT->footer();
