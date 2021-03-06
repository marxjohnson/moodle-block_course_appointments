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
 * Defines class for course appointments block
 *
 * @package block_course_appointments
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

@include_once($CFG->libdir.'/sms/smslib.php');
require_once($CFG->dirroot.'/blocks/course_appointments/course_appointments_form.php');
/**
 *  Course Appointments block class
 *
 *  Extends standard block methods, and defines methods for display,
 *  validation and processing of the form.
 *
 */
class block_course_appointments extends block_base {

    /**
     * Stores the student record during validation and processing
     *
     * @var object
     */
    public $student;
    /**
     * Stores the event start timestamp during validation and processing
     *
     * @var object
     */
    public $timestamp;

    /**
     * Standard block init method, defines the title
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_course_appointments');
    }

    /**
     * Restricts block to course pages
     *
     * @see blocks/block_base#applicable_formats()
     * @return array
     */
    public function applicable_formats() {
        return array('course-view' => true,
                'all' => false);
    }
    /**
     * Prevent multiple instances of the block on a page
     * @return boolean
     */
    public function allow_multiple() {
        return false;
    }

    /**
     * Cron job, sends reminder texts once a day
     *
     * Runs every hour with block's cron job, but only does anything between 8am and 9am (so
     * once a day).  Looks for any appoinments happening today, and if there's a valid mobile
     * number for the student, sends them a reminder via SMS.
     */
    public function cron() {
        global $DB;
        if (date('H') == '08') { // If it's between 8 and 9 AM
            $params = array("%S%", strtotime('today'), strtotime('tomorrow'));
            $where = 'uuid LIKE ? AND timestart BETWEEN ? AND ?';
            $appointments = $DB->get_records_select('event', $where, $params);
            foreach ($appointments as $appointment) {
                $student = get_record('user', 'id', $appointment->userid);
                $teacheruuid = str_replace('S', 'T', $appointment->uuid);
                $teacher_appointment = get_record('event', 'uuid = '.$teacheruuid);
                $teacher = get_record('user', 'id', $teacher_appointment->userid);
                $a = new stdClass;
                $a->name = fullname($teacher);
                $a->time = date('H:i', $appointment->timestart);
                if (class_exists('SMS')) {
                    $sms = SMS::Loader($CFG);
                    if ($sms->format_number($student->phone2)) {
                        $reminder = get_string('remindsms', 'block_course_appointments', $a);
                        $sent = $sms->send_message(array($student->phone2), $reminder);
                    }
                }
            }
        }
    }

    /**
     * Displays the block containing an appointment form
     *
     * Checks that the user has permission to use the block, and if they do, displays the
     * booking form generated by {@see build_form()}
     * Also loads the block's javascript module for displaying the calendar selector.
     *
     * @see blocks/block_base#get_content()
     */
    public function get_content() {
        global $CFG, $COURSE, $SESSION, $DB;
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content->text = '';
        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        if (has_capability('block/course_appointments:book', $coursecontext)) {
            if (isset($SESSION->course_appointments['errors'])) {
                $this->display_errors($SESSION->course_appointments['errors']);
            }

            $url = new moodle_url('/blocks/course_appointments/process.php');
            $customdata = array('coursecontext' => $coursecontext);
            $mform = new course_appointments_form($url->out(), $customdata);
            $form = $mform->display();
            $this->content->text .= $form;
        }

        $this->content->footer = '';
        return $this->content;
    }

    /**
     * Formats the error messages as HTML.
     *
     * @param $errors error messages generated by {@see validate_form()}
     */
    public function display_errors($errors) {
        $this->content->text .= html_writer::start_tag('div', array('class' => "errors"));
        foreach ($errors as $error) {
            $this->content->text .= $error.html_writer::empty_tag('br');
        }
        $this->content->text .= html_writer::end_tag('div');
    }
}
