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
 * English Language Strings for course appointments block
 *
 * @package block_course_appointments
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2011 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Appointments';
$string['pluginnameplural'] = 'Appointments';
$string['book'] = 'Book';
$string['course_appointments:addinstance'] = 'Can add an instance of the Course Appointments block';
$string['course_appointments:book'] = 'Can book course appointments with other users';
$string['course_appointments:bookable'] = 'User can have a course appointment booked with them';
$string['datetime'] = 'Date/Time';
$string['entrydescription'] = 'Meeting between {$a->student} and {$a->teacher}';
$string['entryname'] = 'Meeting with {$a}';
$string['invaliddate'] = 'The date and time selected was invalid';
$string['nostudent'] = 'No Student was selected';
$string['nodate'] = 'No Date was selected';
$string['notnotified'] = 'The student could not be notified';
$string['notifystudent'] = 'Notify Student?';
$string['notifysubject'] = 'Appointment to see {$a}';
$string['notifytext'] = 'Hi {$a->student}
{$a->teacher} has booked an appointment to see you on {$a->date} at {$a->time}.
Please let {$a->teacher} know if you are unable to attend.';
$string['notifysms'] = '{$a->teacher} has booked an appointment to see you on {$a->date} at
{$a->time}. Please let them know if you are unable to attend.';
$string['remindsms'] = 'Don\'t forget your appointment with {$a->name} at {$a->time} today.';
$string['pastdate'] = 'The time and date selected is in the past';
$string['studentdoesntexist'] = 'The selected student doesn\'t exist';
$string['student'] = 'Student';
