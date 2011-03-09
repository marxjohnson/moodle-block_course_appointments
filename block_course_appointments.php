<?php
require_once($CFG->libdir.'/sms/smslib.php');
require_once($CFG->libdir.'/bennu/bennu.class.php');
class block_course_appointments extends block_base {

    var $student;
    var $timestamp;

    function init() {
        $this->title = get_string('blockname', 'block_course_appointments');
    }

    function applicable_formats() {
        return array('course-view' => true,
                'all' => false);
    }

    function cron() {
        if (date('H') == '08') { // If it's between 8 and 9 AM
            $appointments = get_records_select('event', 'uuid LIKE "%S%" AND timestart BETWEEN UNIX_TIMESTAMP(CURDATE()) AND UNIX_TIMESTAMP(CURDATE()+1)');
            foreach ($appointments as $appointment) {
                $student = get_record('user', 'id', $appointment->userid);
                $teacher_appointment = get_record('event', 'uuid = '.str_replace('S', 'T', $appointment->uuid));
                $teacher = get_record('user', 'id', $teacher_appointment->userid);
                $a = new stdClass;
                $a->name = fullname($teacher);
                $a->time = date('H:i', $appointment->timestart);
                $sms = SMS::Loader($CFG);
                if ($sms->format_number($student->phone2)) {
                    $sent = $sms->send_message(array($student->phone2), get_string('remindsms', 'block_course_appointments', $a));
                }
            }
        }
    }

    function get_content() {
        global $CFG, $COURSE, $SESSION;
        if ($this->content !== NULL) {
            return $this->content;
        }
        $this->content->text = '';
        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        if (has_capability('block/course_appointments:book', $coursecontext)) {
            require_js(array('yui_yahoo',
                    'yui_dom',
                    'yui_event',
                    'yui_calendar',
                    'yui_container',
                    $CFG->wwwroot.'/blocks/course_appointments/js/lib.js'));           

            if (isset($SESSION->course_appointments['errors'])) {
                $this->display_errors($SESSION->course_appointments['errors']);
            }

            $this->build_form($coursecontext);
            unset($SESSION->course_appointments);
        }

        $this->content->footer = '';
        return $this->content;
    }

    function build_form($coursecontext) {
        global $CFG, $COURSE, $SESSION;
        $studentrole = get_record('role', 'shortname', 'student');
        $studentassignments = get_users_from_role_on_context($studentrole, $coursecontext);
        $url = $CFG->wwwroot.'/blocks/course_appointments/process.php';
        $studentlist = array();

        foreach($studentassignments as $studentassignment) {
            $studentrecord = get_record('user', 'id', $studentassignment->userid);
            $studentlist[$studentrecord->id] = fullname($studentrecord);
        }

        $hours = range(0, 23);
        $minutes = range(0, 59);
        $selected_student = null;
        $selected_date = null;
        $selected_time = array('hours' => '', 'minutes' => '');
        $checked = '';

        if (isset($SESSION->course_appointments['appointment_student'])) {
            $selected_student = $SESSION->course_appointments['appointment_student'];
        }
        if (isset($SESSION->course_appointments['appointment_date'])) {
            $selected_date = $SESSION->course_appointments['appointment_date'];
        } else {
            $selected_date = date('d M Y');
        }
        if (isset($SESSION->course_appointments['appointment_time_hours'])) {
            $selected_time['hours'] = $SESSION->course_appointments['appointment_time_hours'];
        } else {
            $selected_time['hours'] = date('H');
        }
        if (isset($SESSION->course_appointments['appointment_time_minutes'])) {
            $selected_time['minutes'] = $SESSION->course_appointments['appointment_time_minutes'];
        } else {
            $selected_time['minutes'] = date('i');
        }
        if (isset($SESSION->course_appointments['appointment_notify']) && $SESSION->course_appointments['appointment_notify'] == 1) {
            $checked = 'checked="checked" ';
        }

        $this->content->text .= '<form id="appointments_form" action="'.$url.'" method="post">
                <input type="hidden" name="courseid" value="'.$COURSE->id.'" />
                <div class="formrow">
                    <label for="appointment_student">'.$studentrole->name.':</label>
                    <select name="appointment_student">
                    <option />';

        foreach($studentlist as $id => $name) {
            $selected = '';
            if ($id == $selected_student) {
                $selected = ' selected="selected"';
            }
            $this->content->text .= '<option value="'.$id.'"'.$selected.'>'.$name.'</option>'."\n";
        }
        $this->content->text .= '</select>
                </div>
                <div class="formrow">
                    <label for="date">'.get_string('date').': </label>
                    <input type="text" id="appointment_date" name="appointment_date" value="'.$selected_date.'" />
                    <button type="button" id="show" title="Show Calendar">
                        <img src="'.$CFG->pixpath.'/i/calendar.gif" alt="Calendar" >
                    </button>
                </div>

                <div class="formrow">
                    <label for="appointment_time_hours">'.get_string('time').':</label>
                    <select name="appointment_time_hours">';
        foreach ($hours as $hour) {
            $h = str_pad($hour, 2, 0, STR_PAD_LEFT);
            $selected = '';
            if ($h == $selected_time['hours']) {
                $selected = ' selected="selected"';
            }
            $this->content->text .= '<option value="'.$h.'"'.$selected.'>'.$h.'</option>';
        }
        $this->content->text .= '</select>:<select name="appointment_time_minutes">';
        foreach ($minutes as $minute) {
            $m = str_pad($minute, 2, 0, STR_PAD_LEFT);
            $selected = '';
            if ($m == $selected_time['minutes']) {
                $selected = ' selected="selected"';
            }
            $this->content->text .= '<option value="'.$m.'"'.$selected.'>'.$m.'</option>';
        }
        $this->content->text .= '</select>
                </div>
                <div class="formrow">
                    <label for="appointment_notify">'.get_string('notifystudent', 'block_course_appointments').'</label>
                    <input type="hidden" name="appointment_notify" value="0" />
                    <input type="checkbox" name="appointment_notify" value="1" '.$checked.'/>
                </div>
                <input type="submit" name="appointment_submit" value="'.get_string('book', 'block_course_appointments').'" />
                </form>';

    }

    function validate_form() {
        global $SESSION;
        $studentid = optional_param('appointment_student', 0, PARAM_INT);
        $SESSION->course_appointments['appointment_student'] = $studentid;
        $datestring = optional_param('appointment_date', 0, PARAM_TEXT);
        $SESSION->course_appointments['appointment_date'] = $datestring;
        $hours = optional_param('appointment_time_hours', 0, PARAM_TEXT);
        $SESSION->course_appointments['appointment_time_hours'] = $hours;
        $mins = optional_param('appointment_time_minutes', 0, PARAM_TEXT);
        $SESSION->course_appointments['appointment_time_mins'] = $mins;
        $this->timestamp = strtotime($datestring.' '.$hours.':'.$mins);
        $errors = array();

        if (empty($studentid)) {
            $errors[] = get_string('nostudent', 'block_course_appointments');
        } else {
            if(!$this->student = get_record('user', 'id', $studentid)) {
                $errors[] = get_string('studentdoesntexist', 'block_course_appointments');
            }
        }

        if (empty($datestring)) {
            $errors[] = get_string('nodate', 'block_course_appointments');
        }

        if (empty($this->timestamp) || $this->timestamp == -1) {
            $errors[] = get_string('invaliddate', 'block_course_appointments');
        } else if ($this->timestamp < time()) {
            $errors[] = get_string('pastdate', 'block_course_appointments');
        }

        if (empty($errors)) {
            return false;
        } else {
            return $errors;
        }
    }

    function process_form() {
        global $USER, $COURSE, $CFG, $SESSION;
        global $sms;
        $notify = optional_param('appointment_notify', 0, PARAM_INT);
        $names = new stdClass;
        $names->student = fullname($this->student);
        $names->teacher = fullname($USER);
        $uuid = Bennu::generate_guid();
        $appointment = new stdClass;
        $appointment->name = get_string('entryname', 'block_course_appointments', $names->student);
        $appointment->description = get_string('entrydescription', 'block_course_appointments', $names);
        $appointment->userid = $USER->id;
        $appointment->timestart = $this->timestamp;

        // To identify the two appointments as linked, we use the same UUID for both, but replace the
        // dashes with T (for Teacher) and S (For student). Since neither character is Hexadecimal, they
        // wont occur in any generated UUID.
        $appointment->uuid = str_replace('-', 'T', $uuid);
        $appointment->format = 1;
        insert_record('event', $appointment);
        $appointment->name = get_string('entryname', 'block_course_appointments', $names->teacher);
        $appointment->userid = $this->student->id;
        $appointment->uuid = str_replace('-', 'S', $uuid);
        insert_record('event', $appointment);
        $names->date = date('d/m/Y', $this->timestamp);
        $names->time = date('H:i', $this->timestamp);
        $notified = false;
        if ($notify) {            
            $notified = email_to_user($this->student, $USER, get_string('notifysubject', 'block_course_appointments', $names->teacher), get_string('notifytext', 'block_course_appointments', $names));
            $sms = SMS::Loader($CFG);
            if ($sms->format_number($this->student->phone2)) {
                $sent = $sms->send_message(array($this->student->phone2), get_string('notifysms', 'block_course_appointments', $names));
                foreach($sent->responses as $response) { // Create a list of users to whom the message failed to send
                    if($response->code == 1) {
                        $notified = true;
                    }
                }
            }
        }
        $SESSION->course_appointments = array();
        if ($notify && !$notified) {
            $SESSION->course_appointments['errors'][] = get_string('notnotified', 'block_course_appointments');
        }
    }

    function display_errors($errors) {
        $this->content->text .= '<div class="errors">';
        foreach ($errors as $error) {
            $this->content->text .= $error.'<br />';
        }
        $this->content->text .= '</div>';
    }
}
?>
