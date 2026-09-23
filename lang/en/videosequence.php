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
 * videosequence.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['addstep'] = 'Add step';
$string['allowseek'] = 'Allow seeking to unwatched parts';
$string['answerunlocksat'] = 'The sequence unlocks at {$a}% watched.';
$string['attemptlimitreached'] = 'You have reached the maximum number of attempts.';
$string['attemptresult'] = 'Attempt {$a->attempt}: {$a->correct} of {$a->total} positions correct ({$a->score}%).';
$string['attempts'] = 'Attempts';
$string['attemptsavedfeedbackhidden'] = 'Attempt {$a} saved. Detailed feedback will be shown on the final attempt.';
$string['attemptstatus'] = 'Attempts made: {$a}';
$string['backtoactivity'] = 'Back to activity';
$string['completed'] = 'Completed';
$string['completiondetail:submit'] = 'Submit at least one sequence attempt';
$string['completiondetail:watch'] = 'Watch at least {$a}% of the video';
$string['completionrules'] = '';
$string['completionsubmit'] = 'Student must submit at least one sequence';
$string['completionwatch'] = 'Student must reach the minimum watched percentage';
$string['confirmdelete'] = 'Delete this step?';
$string['correctsteps'] = 'Correct steps';
$string['createinstructions'] = 'Type each stage in the order demonstrated in the video.';
$string['editstep'] = 'Edit step';
$string['errormaxfiles'] = 'Only one file can be uploaded.';
$string['errornonnegative'] = 'Enter zero or a positive number.';
$string['errorpercent'] = 'Enter a percentage from 0 to 100.';
$string['errortimeend'] = 'End time must be after start time.';
$string['errorvideourl'] = 'Enter the video URL for this source.';
$string['feedbackfinal'] = 'Show only on the final allowed attempt';
$string['feedbackimmediate'] = 'Show after every attempt';
$string['feedbackmode'] = 'Feedback';
$string['fillallsteps'] = 'Complete every step before submitting.';
$string['incomplete'] = 'Incomplete';
$string['incompleteanswer'] = 'Incomplete sequence';
$string['invalidanswer'] = 'The submitted sequence is invalid.';
$string['managesequence'] = 'Manage sequence';
$string['maxattempts'] = 'Maximum attempts';
$string['maxattempts_help'] = 'Use 0 for unlimited attempts.';
$string['minwatchpercent'] = 'Minimum watched percentage before answering';
$string['modecreate'] = 'Create the sequence from memory';
$string['modereorder'] = 'Reorder the provided steps';
$string['modulename'] = 'Video Sequence';
$string['modulenameplural'] = 'Video Sequences';
$string['noreportdata'] = 'No enrolled students were found.';
$string['nosteps'] = 'No steps have been configured.';
$string['nostepsstudent'] = 'The teacher has not configured sequence steps yet.';
$string['notenoughwatched'] = 'You must watch at least {$a}% of the video before submitting.';
$string['pluginadministration'] = 'Video Sequence administration';
$string['pluginname'] = 'Video Sequence';
$string['poster'] = 'Poster image';
$string['privacy:metadata:attempts'] = 'Stores sequence attempts submitted by students.';
$string['privacy:metadata:attempts:answer'] = 'The submitted step order or typed sequence.';
$string['privacy:metadata:attempts:grade'] = 'The grade calculated for the attempt.';
$string['privacy:metadata:attempts:score'] = 'The calculated percentage score.';
$string['privacy:metadata:attempts:userid'] = 'The user who submitted the attempt.';
$string['privacy:metadata:external:videourl'] = 'The configured public video URL may be sent to the external video provider.';
$string['privacy:metadata:progress'] = 'Stores each student’s watched-video progress.';
$string['privacy:metadata:progress:lastposition'] = 'The last watched video position.';
$string['privacy:metadata:progress:percent'] = 'The percentage of unique video content watched.';
$string['privacy:metadata:progress:userid'] = 'The user whose progress is stored.';
$string['privacy:metadata:progress:watchedsegments'] = 'The unique video intervals watched by the user.';
$string['privacy:metadata:vimeo'] = 'Vimeo may receive normal player request data when a Vimeo video is used.';
$string['privacy:metadata:youtube'] = 'YouTube may receive normal player request data when a YouTube video is used.';
$string['remainingattempts'] = 'Remaining attempts: {$a}';
$string['reorderinstructions'] = 'Drag the steps into the correct order, or use the arrow buttons.';
$string['report'] = 'Report';
$string['resetuserdata'] = 'Delete Video Sequence progress and attempts';
$string['resumeplayback'] = 'Resume from the last watched position';
$string['reviewclip'] = 'Review clip';
$string['savestep'] = 'Save step';
$string['sequenceheader'] = 'Sequence settings';
$string['sequencemode'] = 'Student response mode';
$string['sequencemode_help'] = 'Reorder mode shows the configured steps shuffled. Create mode hides their names and asks the student to type the sequence.';
$string['sourceupload'] = 'Uploaded video';
$string['sourceurl'] = 'Direct video URL';
$string['sourcevimeo'] = 'Vimeo';
$string['sourceyoutube'] = 'YouTube';
$string['status'] = 'Status';
$string['stepaliases'] = 'Accepted alternative names';
$string['stepaliases_help'] = 'One alternative per line, or separated by semicolons. Used only in create mode.';
$string['stepdescription'] = 'Description';
$string['steptitle'] = 'Step name';
$string['student'] = 'Student';
$string['submitsequence'] = 'Submit sequence';
$string['submittedsequence'] = 'Submitted sequence';
$string['timeend'] = 'Video end time (seconds, optional)';
$string['timestart'] = 'Video start time (seconds)';
$string['videofile'] = 'Video file';
$string['videoheader'] = 'Video';
$string['videosequence:addinstance'] = 'Add a new Video Sequence activity';
$string['videosequence:manage'] = 'Manage Video Sequence steps';
$string['videosequence:view'] = 'View Video Sequence activities';
$string['videosequence:viewreports'] = 'View Video Sequence reports';
$string['videosequencename'] = 'Video Sequence name';
$string['videosource'] = 'Video source';
$string['videotime'] = 'Video time';
$string['videourl'] = 'Video URL';
$string['watchbeforeanswer'] = 'Watch at least {$a}% of the video before answering.';
$string['watched'] = 'Watched';
$string['yoursequence'] = 'Your sequence';
