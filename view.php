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
 * view.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videosequence\event\course_module_viewed;

require_once('../../config.php');
$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('videosequence', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videosequence', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videosequence:view', $context);

$PAGE->set_url('/mod/videosequence/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$event = course_module_viewed::create(['objectid' => $activity->id, 'context' => $context]);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('videosequence', $activity);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$steps = array_values($DB->get_records('videosequence_steps', ['videosequenceid' => $activity->id], 'position ASC'));
$progress = $DB->get_record('videosequence_progress', ['videosequenceid' => $activity->id, 'userid' => $USER->id]);
$progress = $progress ?: (object)['percent' => 0, 'lastposition' => 0];
$attempts = $DB->count_records('videosequence_attempts', ['videosequenceid' => $activity->id, 'userid' => $USER->id]);
$remaining = (int)$activity->maxattempts > 0 ? max(0, (int)$activity->maxattempts - $attempts) : -1;
$cananswer = (float)$progress->percent >= (float)$activity->minwatchpercent && $steps && ($remaining !== 0);

$posterurl = '';
$fs = get_file_storage();
$posterfiles = $fs->get_area_files($context->id, 'mod_videosequence',
    'poster', 0, 'id', false);
if ($posterfiles) {
    $file = reset($posterfiles);
    $posterurl = moodle_url::make_pluginfile_url($context->id, 'mod_videosequence',
        'poster', 0, '/', $file->get_filename())->out(false);
}
$videourl = '';
if ($activity->videosource === 'upload') {
    $videofiles = $fs->get_area_files($context->id, 'mod_videosequence',
        'video', 0, 'id', false);
    if ($videofiles) {
        $file = reset($videofiles);
        $videourl = moodle_url::make_pluginfile_url($context->id, 'mod_videosequence',
            'video', 0, '/', $file->get_filename())->out(false);
    }
} else {
    $videourl = (string)$activity->videourl;
}

$sourceinfo = \mod_videosequence\video_source::prepare($activity->videosource, $videourl);
$studentsteps = [];
if ((int)$activity->sequencemode === 0) {
    $shuffled = $steps;
    if (count($shuffled) > 1) {
        $seed = crc32($USER->id . ':' . $activity->id);
        mt_srand($seed);
        shuffle($shuffled);
        mt_srand();
    }
    foreach ($shuffled as $step) {
        $studentsteps[] = [
            'id' => (int)$step->id,
            'title' => format_string($step->title),
            'description' => format_text($step->description, FORMAT_PLAIN),
            'timestart' => (float)$step->timestart,
            'timeend' => (float)$step->timeend,
            'hasvideo' => (float)$step->timestart > 0 || (float)$step->timeend > 0,
        ];
    }
}

$data = [
    'cmid' => $cm->id,
    'name' => format_string($activity->name),
    'intro' => format_module_intro('videosequence', $activity, $cm->id),
    'source' => $activity->videosource,
    'videourl' => $videourl,
    'embedurl' => $sourceinfo['embedurl'],
    'html5source' => $sourceinfo['html5'],
    'youtubesource' => $sourceinfo['youtube'],
    'vimeosource' => $sourceinfo['vimeo'],
    'posterurl' => $posterurl,
    'resume' => (bool)$activity->resumeplayback,
    'allowseek' => (bool)$activity->allowseek,
    'lastposition' => (float)$progress->lastposition,
    'percent' => (float)$progress->percent,
    'minwatchpercent' => (int)$activity->minwatchpercent,
    'cananswer' => $cananswer,
    'nosteps' => !$steps,
    'reordermode' => (int)$activity->sequencemode === 0,
    'createmode' => (int)$activity->sequencemode === 1,
    'steps' => $studentsteps,
    'stepcount' => count($steps),
    'blanksteps' => array_map(static fn($number) => ['number' => $number], range(1, max(1, count($steps)))),
    'attempts' => $attempts,
    'remaining' => $remaining,
    'unlimited' => $remaining < 0,
    'manageurl' => (new moodle_url('/mod/videosequence/manage.php', ['id' => $cm->id]))->out(false),
    'reporturl' => (new moodle_url('/mod/videosequence/report.php', ['id' => $cm->id]))->out(false),
    'canmanage' => has_capability('mod/videosequence:manage', $context),
    'canreport' => has_capability('mod/videosequence:viewreports', $context),
];
$PAGE->requires->strings_for_js([
    'incompleteanswer',
    'fillallsteps',
    'attemptresult',
    'attemptsavedfeedbackhidden',
], 'videosequence');
$PAGE->requires->js_call_amd('mod_videosequence/player', 'init', [[
    'cmid' => $cm->id, 'source' => $activity->videosource, 'url' => $videourl,
    'lastposition' => (float)$progress->lastposition, 'resume' => (bool)$activity->resumeplayback,
    'allowseek' => (bool)$activity->allowseek,
]]);
$PAGE->requires->js_call_amd('mod_videosequence/sequence', 'init', [[
    'cmid' => $cm->id, 'mode' => (int)$activity->sequencemode, 'minwatch' => (int)$activity->minwatchpercent,
]]);
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_videosequence/view', $data);
echo $OUTPUT->footer();
