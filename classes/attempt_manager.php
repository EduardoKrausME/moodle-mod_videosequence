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
 * attempt_manager.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videosequence;

use moodle_exception;
use stdClass;

/**
 * Grades and stores sequence attempts.
 */
class attempt_manager {
    /**
     * Method submit.
     *
     * @param stdClass $activity Parameter activity.
     * @param int $userid Parameter userid.
     * @param array $answer Parameter answer.
     * @return array Return value.
     */
    public function submit(stdClass $activity, int $userid, array $answer): array {
        global $DB;
        $progress = $DB->get_record('videosequence_progress', ['videosequenceid' => $activity->id, 'userid' => $userid]);
        $percent = $progress ? (float)$progress->percent : 0.0;
        if ($percent + 0.001 < (float)$activity->minwatchpercent) {
            throw new moodle_exception('notenoughwatched', 'videosequence', '', $activity->minwatchpercent);
        }
        $attemptcount = $DB->count_records('videosequence_attempts', ['videosequenceid' => $activity->id, 'userid' => $userid]);
        if ((int)$activity->maxattempts > 0 && $attemptcount >= (int)$activity->maxattempts) {
            throw new moodle_exception('attemptlimitreached', 'videosequence');
        }
        $steps = array_values($DB->get_records('videosequence_steps', ['videosequenceid' => $activity->id], 'position ASC'));
        if (!$steps) {
            throw new moodle_exception('nosteps', 'videosequence');
        }
        $total = count($steps);
        if (count($answer) !== $total) {
            throw new moodle_exception('invalidanswer', 'videosequence');
        }
        $correct = 0;
        $positions = [];
        if ((int)$activity->sequencemode === 0) {
            $submitted = array_map('intval', $answer);
            if (count(array_unique($submitted)) !== $total) {
                throw new moodle_exception('invalidanswer', 'videosequence');
            }
            $validids = array_map(static fn($step): int => (int)$step->id, $steps);
            foreach ($submitted as $stepid) {
                if (!in_array($stepid, $validids, true)) {
                    throw new moodle_exception('invalidanswer', 'videosequence');
                }
            }
            foreach ($steps as $index => $step) {
                $iscorrect = ($submitted[$index] ?? 0) === (int)$step->id;
                $positions[] = $iscorrect;
                if ($iscorrect) {
                    $correct++;
                }
            }
        } else {
            foreach ($steps as $index => $step) {
                $value = trim((string)($answer[$index] ?? ''));
                if ($value === '') {
                    throw new moodle_exception('invalidanswer', 'videosequence');
                }
                $iscorrect = $this->matches_step($value, $step);
                $positions[] = $iscorrect;
                if ($iscorrect) {
                    $correct++;
                }
            }
        }
        $score = $total ? round(($correct / $total) * 100, 2) : 0.0;
        $grade = round(($score / 100) * (float)$activity->grade, 5);
        $now = time();
        $record = (object)[
            'videosequenceid' => $activity->id, 'userid' => $userid, 'attemptno' => $attemptcount + 1,
            'answerjson' => json_encode(array_values($answer), JSON_UNESCAPED_UNICODE), 'correctcount' => $correct,
            'totalsteps' => $total, 'score' => $score, 'grade' => $grade, 'timecreated' => $now, 'timemodified' => $now,
        ];
        $record->id = $DB->insert_record('videosequence_attempts', $record);
        videosequence_update_grades($activity, $userid);
        $showfeedback = (bool)$activity->feedbackmode || (int)$activity->maxattempts === 0 ||
            $record->attemptno >= (int)$activity->maxattempts;
        $canretry = (int)$activity->maxattempts === 0 || $record->attemptno < (int)$activity->maxattempts;
        return [
            'attempt' => $record->attemptno, 'correct' => $showfeedback ? $correct : -1,
            'total' => $total, 'score' => $showfeedback ? $score : -1, 'grade' => $showfeedback ? $grade : -1,
            'feedbackavailable' => $showfeedback,
            'positionresults' => $showfeedback ? $positions : [],
            'canretry' => $canretry,
        ];
    }

    /**
     * Method matches_step.
     *
     * @param string $value Parameter value.
     * @param stdClass $step Parameter step.
     * @return bool Return value.
     */
    private function matches_step(string $value, stdClass $step): bool {
        $normalize = static function (string $text): string {
            $text = core_text::strtolower(trim($text));
            $text = preg_replace('/\\s+/u', ' ', $text);
            return $text ?? '';
        };
        $needle = $normalize($value);
        if ($needle === $normalize($step->title)) {
            return true;
        }
        foreach (preg_split('/[\\r\\n;]+/', (string)$step->aliases) ?: [] as $alias) {
            if ($alias !== '' && $needle === $normalize($alias)) {
                return true;
            }
        }
        return false;
    }
}
