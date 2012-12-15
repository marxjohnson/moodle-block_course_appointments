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
 * Defines the form for making appointments
 *
 * @package block_course_appointments
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die();
}
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/bennu/bennu.class.php');

class course_appointments_form extends moodleform {

    public function definition() {
        $mform = $this->_form;
        $coursecontext = $this->_customdata['coursecontext'];
        $cap = 'block/course_appointments:bookable';
        $students = get_users_by_capability($coursecontext, $cap, '', 'lastname, firstname');
        $studentlist = array();
        foreach ($students as $student) {
            $studentlist[$student->id] = fullname($student);
        }

        $mform->addElement('hidden', 'courseid', $coursecontext->instanceid);

        $mform->addElement('select',
                           'student',
                           get_string('student', 'block_course_appointments'),
                           $studentlist);
        $mform->addRule('student', null, 'required', null, 'client');

        $years = array('startyear' => date('Y'), 'stopyear' => date('Y')+1, 'optional' => false);
        $mform->addElement('date_time_selector',
                           'date',
                           get_string('datetime', 'block_course_appointments'),
                           $years);
        $mform->addRule('date', null, 'required', null, 'client');
        $mform->addElement('checkbox',
                           'notify',
                           get_string('notifystudent', 'block_course_appointments'));
        $mform->addElement('submit', 'book', get_string('book', 'block_course_appointments'));

    }

    public function validate($data) {
        if (empty($data['date']) || $data['date'] == -1) {
            $errors['date'] = get_string('invaliddate', 'block_course_appointments');
        } else if ($data['date'] < time()) {
            $errors['date'] = get_string('pastdate', 'block_course_appointments');
        }
        return $errors;
    }

    /**
     * Generate the HTML for the form, capture it in an output buffer, then return it
     *
     * @return string
     */
    public function display() {
        //finalize the form definition if not yet done
        if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }
        ob_start();
        $this->_form->display();
        $form = ob_get_clean();

        return $form;
    }

    public function process($data) {
        global $USER, $COURSE, $CFG, $DB;
        global $sms;
        $student = $DB->get_record('user', array('id' => $data->student));
        $names = new stdClass;
        $names->student = fullname($student);
        $names->teacher = fullname($USER);
        $uuid = Bennu::generate_guid();
        $appointment = new stdClass;
        $appointment->name = get_string('entryname', 'block_course_appointments', $names->student);
        $appointment->description = get_string('entrydescription',
                                               'block_course_appointments',
                                               $names);
        $appointment->userid = $USER->id;
        $appointment->timestart = $data->date;

        // To identify the two appointments as linked, we use the same UUID for both, but replace
        // the dashes with T (for Teacher) and S (For student). Since neither character is
        // Hexadecimal, they wont occur in any generated UUID.
        $appointment->uuid = str_replace('-', 'T', $uuid);
        $appointment->format = 1;
        $DB->insert_record('event', $appointment);
        $appointment->name = get_string('entryname', 'block_course_appointments', $names->teacher);
        $appointment->userid = $data->student;
        $appointment->uuid = str_replace('-', 'S', $uuid);
        $DB->insert_record('event', $appointment);
        $names->date = date('d/m/Y', $data->date);
        $names->time = date('H:i', $data->date);
        $notified = false;
        if ($data->notify) {
            $subject = get_string('notifysubject', 'block_course_appointments', $names->teacher);
            $message = get_string('notifytext', 'block_course_appointments', $names);
            $notified = email_to_user($student, $USER, $subject, $message);
            if (class_exists('SMS')) {
                $sms = SMS::Loader($CFG);
                if ($sms->format_number($student->phone2)) {
                    $message = get_string('notifysms', 'block_course_appointments', $names);
                    $sent = $sms->send_message(array($student->phone2), $message);
                    // Create a list of users to whom the message failed to send
                    foreach ($sent->responses as $response) {
                        if ($response->code == 1) {
                            $notified = true;
                        }
                    }
                }
            }
        }
        $SESSION->course_appointments = array();
        if ($data->notify && !$notified) {
            $SESSION->course_appointments['errors'][] = get_string('notnotified',
                                                                   'block_course_appointments');
        }
    }
}
