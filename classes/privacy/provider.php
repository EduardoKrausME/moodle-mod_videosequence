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
 * provider.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosequence\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy API implementation.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('videosequence_progress', [
            'userid' => 'privacy:metadata:progress:userid',
            'lastposition' => 'privacy:metadata:progress:lastposition',
            'watchedsegments' => 'privacy:metadata:progress:watchedsegments',
            'percent' => 'privacy:metadata:progress:percent',
        ], 'privacy:metadata:progress');
        $collection->add_database_table('videosequence_attempts', [
            'userid' => 'privacy:metadata:attempts:userid',
            'answerjson' => 'privacy:metadata:attempts:answer',
            'score' => 'privacy:metadata:attempts:score',
            'grade' => 'privacy:metadata:attempts:grade',
        ], 'privacy:metadata:attempts');
        $collection->add_external_location_link('youtube',
            ['videourl' => 'privacy:metadata:external:videourl'], 'privacy:metadata:youtube');
        $collection->add_external_location_link('vimeo',
            ['videourl' => 'privacy:metadata:external:videourl'], 'privacy:metadata:vimeo');
        return $collection;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT DISTINCT ctx.id FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {videosequence} v ON v.id = cm.instance
             LEFT JOIN {videosequence_progress} p ON p.videosequenceid = v.id AND p.userid = :userid1
             LEFT JOIN {videosequence_attempts} a ON a.videosequenceid = v.id AND a.userid = :userid2
                 WHERE p.id IS NOT NULL OR a.id IS NOT NULL";
        $contextlist->add_from_sql($sql,
            ['contextlevel' => CONTEXT_MODULE, 'modname' => 'videosequence', 'userid1' => $userid, 'userid2' => $userid]);
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist for the context.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }

        $params = [
            'cmid' => $context->instanceid,
            'modname' => 'videosequence',
        ];

        $sql = "SELECT p.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {videosequence_progress} p ON p.videosequenceid = cm.instance
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql('userid', $sql, $params);

        $sql = "SELECT a.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {videosequence_attempts} a ON a.videosequenceid = cm.instance
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $cm = get_coursemodule_from_id('videosequence', $context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }
            $activity = $DB->get_record('videosequence', ['id' => $cm->instance]);
            if (!$activity) {
                continue;
            }
            $progress = $DB->get_record('videosequence_progress',
                ['videosequenceid' => $activity->id, 'userid' => $userid]);
            $attempts = $DB->get_records('videosequence_attempts',
                ['videosequenceid' => $activity->id, 'userid' => $userid], 'attemptno ASC');
            $data = (object)[
                'progress' => $progress ? (object)[
                    'percent' => $progress->percent,
                    'lastposition' => $progress->lastposition,
                    'totalwatchtime' => $progress->totalwatchtime,
                    'timemodified' => transform::datetime($progress->timemodified),
                ] : null,
                'attempts' => array_values(array_map(static fn($attempt) => (object)[
                    'attempt' => $attempt->attemptno,
                    'answer' => $attempt->answerjson,
                    'score' => $attempt->score,
                    'grade' => $attempt->grade,
                    'timecreated' => transform::datetime($attempt->timecreated),
                ], $attempts)),
            ];
            writer::with_context($context)->export_data([], $data);
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param \context $context Parameter context.
     * @return void Return value.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('videosequence', $context->instanceid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return;
        }
        $DB->delete_records('videosequence_progress', ['videosequenceid' => $cm->instance]);
        $DB->delete_records('videosequence_attempts', ['videosequenceid' => $cm->instance]);
    }

    /**
     * Delete data for multiple users within a single context.
     *
     * @param approved_userlist $userlist Approved users and context.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('videosequence', $context->instanceid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $select = 'videosequenceid = :videosequenceid AND userid ' . $usersql;
        $params = ['videosequenceid' => $cm->instance] + $userparams;

        $DB->delete_records_select('videosequence_progress', $select, $params);
        $DB->delete_records_select('videosequence_attempts', $select, $params);
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $cm = get_coursemodule_from_id('videosequence', $context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }
            $DB->delete_records('videosequence_progress', ['videosequenceid' => $cm->instance, 'userid' => $userid]);
            $DB->delete_records('videosequence_attempts', ['videosequenceid' => $cm->instance, 'userid' => $userid]);
        }
    }
}
