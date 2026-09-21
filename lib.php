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
 * Main callbacks for Video Sequence.
 *
 * @package mod_videosequence
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Declares supported Moodle features.
 *
 * @param string $feature Moodle feature.
 * @return bool|int|string|null
 */
function videosequence_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_ASSESSMENT;
        default:
            return null;
    }
}

/**
 * Adds an instance.
 *
 * @param stdClass $data Submitted data.
 * @param mod_videosequence_mod_form|null $mform Form.
 * @return int
 */
function videosequence_add_instance(stdClass $data, ?mod_videosequence_mod_form $mform = null): int {
    global $DB;
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $id = $DB->insert_record('videosequence', $data);
    $data->id = $id;
    videosequence_save_video_files($data);
    videosequence_grade_item_update($data);
    return $id;
}

/**
 * Updates an instance.
 *
 * @param stdClass $data Submitted data.
 * @param mod_videosequence_mod_form|null $mform Form.
 * @return bool
 */
function videosequence_update_instance(stdClass $data, ?mod_videosequence_mod_form $mform = null): bool {
    global $DB;
    $data->id = $data->instance;
    $data->timemodified = time();
    $result = $DB->update_record('videosequence', $data);
    videosequence_save_video_files($data);
    videosequence_grade_item_update($data);
    return $result;
}

/**
 * Saves uploaded video and poster files.
 *
 * @param stdClass $data Activity data.
 * @return void
 */
function videosequence_save_video_files(stdClass $data): void {
    if (empty($data->coursemodule)) {
        return;
    }
    $context = context_module::instance($data->coursemodule);
    if (isset($data->videofile)) {
        file_save_draft_area_files((int)$data->videofile, $context->id, 'mod_videosequence', 'video', 0, [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['video'],
        ]);
    }
    if (isset($data->poster)) {
        file_save_draft_area_files((int)$data->poster, $context->id, 'mod_videosequence', 'poster', 0, [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['image'],
        ]);
    }
}

/**
 * Deletes an instance.
 *
 * @param int $id Instance id.
 * @return bool
 */
function videosequence_delete_instance(int $id): bool {
    global $DB;
    $activity = $DB->get_record('videosequence', ['id' => $id]);
    if (!$activity) {
        return false;
    }
    $transaction = $DB->start_delegated_transaction();
    $DB->delete_records('videosequence_attempts', ['videosequenceid' => $id]);
    $DB->delete_records('videosequence_progress', ['videosequenceid' => $id]);
    $DB->delete_records('videosequence_steps', ['videosequenceid' => $id]);
    $DB->delete_records('videosequence', ['id' => $id]);
    $transaction->allow_commit();
    videosequence_grade_item_delete($activity);
    return true;
}

/**
 * Serves uploaded files.
 */
function mod_videosequence_pluginfile($course, $cm, $context, string $filearea, array $args,
                                      bool $forcedownload, array $options = []): bool {
    if ($context->contextlevel !== CONTEXT_MODULE || !in_array($filearea, ['video', 'poster'], true)) {
        return false;
    }
    require_login($course, true, $cm);
    require_capability('mod/videosequence:view', $context);
    $filename = array_pop($args);
    $filepath = '/' . ($args ? implode('/', $args) . '/' : '');
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_videosequence', $filearea, 0, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Creates or updates the grade item.
 *
 * @param stdClass $activity Activity.
 * @param array|null $grades Grades.
 * @return int
 */
function videosequence_grade_item_update(stdClass $activity, ?array $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    $item = [
        'itemname' => clean_param($activity->name, PARAM_NOTAGS),
        'gradetype' => (float)$activity->grade > 0 ? GRADE_TYPE_VALUE : GRADE_TYPE_NONE,
        'grademin' => 0,
        'grademax' => max(0, (float)$activity->grade),
    ];
    return grade_update('mod/videosequence', $activity->course, 'mod', 'videosequence', $activity->id, 0, $grades, $item);
}

/**
 * Updates grades from the best attempt.
 *
 * @param stdClass $activity Activity.
 * @param int $userid Optional user.
 * @param bool $nullifnone Whether to clear missing grade.
 * @return void
 */
function videosequence_update_grades(stdClass $activity, int $userid = 0, bool $nullifnone = true): void {
    global $DB;
    $params = ['activityid' => $activity->id];
    $usersql = '';
    if ($userid) {
        $usersql = ' AND userid = :userid';
        $params['userid'] = $userid;
    }
    $records = $DB->get_records_sql(
        'SELECT userid, MAX(grade) AS grade FROM {videosequence_attempts} WHERE videosequenceid = :activityid' .
        $usersql . ' GROUP BY userid',
        $params
    );
    $grades = [];
    foreach ($records as $record) {
        $grades[$record->userid] = (object)['userid' => $record->userid, 'rawgrade' => (float)$record->grade];
    }
    if (!$grades && $userid && $nullifnone) {
        $grades[$userid] = (object)['userid' => $userid, 'rawgrade' => null];
    }
    videosequence_grade_item_update($activity, $grades);
}

/**
 * Deletes grade item.
 *
 * @param stdClass $activity Activity.
 * @return int
 */
function videosequence_grade_item_delete(stdClass $activity): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    return grade_update('mod/videosequence', $activity->course, 'mod', 'videosequence', $activity->id, 0, null, ['deleted' => 1]);
}

/**
 * Prepares course module cached information.
 *
 * @param stdClass $cm Course module.
 * @return cached_cm_info|null
 */
function videosequence_get_coursemodule_info(stdClass $cm): ?cached_cm_info {
    global $DB;
    $activity = $DB->get_record('videosequence', ['id' => $cm->instance],
        'id,name,intro,introformat,minwatchpercent,completionwatch,completionsubmit');
    if (!$activity) {
        return null;
    }
    $info = new cached_cm_info();
    $info->name = $activity->name;
    if ($cm->showdescription) {
        $info->content = format_module_intro('videosequence', $activity, $cm->id, false);
    }
    $info->customdata = [
        'customcompletionrules' => [
            'completionwatch' => (bool)$activity->completionwatch,
            'completionsubmit' => (bool)$activity->completionsubmit,
            'minwatchpercent' => (int)$activity->minwatchpercent,
        ],
    ];
    return $info;
}

/**
 * Descriptions for custom completion rules.
 *
 * @param cached_cm_info $cm Cached module info.
 * @return array
 */
function videosequence_get_completion_active_rule_descriptions(cached_cm_info $cm): array {
    $rules = $cm->customdata['customcompletionrules'] ?? [];
    $descriptions = [];
    if (!empty($rules['completionwatch'])) {
        $descriptions[] = get_string('completiondetail:watch', 'videosequence', $rules['minwatchpercent']);
    }
    if (!empty($rules['completionsubmit'])) {
        $descriptions[] = get_string('completiondetail:submit', 'videosequence');
    }
    return $descriptions;
}

/**
 * Reset course user data.
 *
 * @param stdClass $data Reset data.
 * @return array
 */
function videosequence_reset_userdata(stdClass $data): array {
    global $DB;
    $status = [];
    $instances = $DB->get_records('videosequence', ['course' => $data->courseid], '', 'id');
    foreach ($instances as $instance) {
        $DB->delete_records('videosequence_attempts', ['videosequenceid' => $instance->id]);
        $DB->delete_records('videosequence_progress', ['videosequenceid' => $instance->id]);
    }
    $status[] = [
        'component' => get_string('modulenameplural', 'videosequence'),
        'item' => get_string('resetuserdata', 'videosequence'),
        'error' => false,
    ];
    return $status;
}
