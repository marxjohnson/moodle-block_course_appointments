<?php
@require_once($CFG->libdir.'/sms/smslib.php');
require_once($CFG->libdir.'/bennu/bennu.class.php');
class block_course_appointments extends block_base {

    var $student;
    var $timestamp;

    function init() {
        $this->title = get_string('pluginname', 'block_course_appointments');
    }

    function applicable_formats() {
        return array('course-view' => true,
                'all' => false);
    }
    
    function allow_multiple() {
        return false;
    }
   
    function cron() {
        global $DB;
        if (date('H') == '08') { // If it's between 8 and 9 AM
            $params = array("%S%", strtotime('today'), strtotime('tomorrow'));
            $appointments = $DB->get_records_select('event', 'uuid LIKE ? AND timestart BETWEEN ? AND ?', $params);
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
        global $CFG, $COURSE, $SESSION, $DB;
        if ($this->content !== NULL) {
            return $this->content;
        }
        $this->content->text = '';
        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        if (has_capability('block/course_appointments:book', $coursecontext)) {
            if (isset($SESSION->course_appointments['errors'])) {
                $this->display_errors($SESSION->course_appointments['errors']);
            }

           $this->content->text .= $this->build_form($coursecontext);
            unset($SESSION->course_appointments);
        }

        $this->content->footer = '';
        $jsmodule = array(
            'name'  =>  'block_course_appointments',
            'fullpath'  =>  '/blocks/course_appointments/module.js',
            'requires'  =>  array('base', 'node')
        );

        $this->page->requires->js_init_call('M.block_course_appointments.init', array(), false, $jsmodule);
        return $this->content;
    }

    function build_form($coursecontext) {
        global $CFG, $COURSE, $SESSION, $DB, $OUTPUT;
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $studentassignments = get_users_from_role_on_context($studentrole, $coursecontext);
        $url = new moodle_url('/blocks/course_appointments/process.php');
        $studentlist = array();

        foreach($studentassignments as $studentassignment) {
            $studentrecord = $DB->get_record('user', array('id' => $studentassignment->userid));
            $studentlist[$studentrecord->id] = fullname($studentrecord);
        }

        $hours = range(0, 23);
        $minutes = range(0, 59);
        foreach (array('hours', 'minutes') as $array) {
            foreach (${$array} as $k => $v) {
                ${$array}[$k] = str_pad($v, 2, '0', STR_PAD_LEFT);
            }
        }
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
            $checked = 'checked';
        }

        $form = html_writer::start_tag('form', array('id' => "block_courseappointments_form", 'action' => $url, 'method' => "post"));
        $form .= html_writer::empty_tag('input', array('type' => "hidden", 'name' => "courseid", 'value' => $COURSE->id));
        $form .= html_writer::start_tag('div', array('class' => "formrow"));
        $form .= html_writer::tag('label', $studentrole->name, array('for' => "appointment_student"));
        $form .= html_writer::select($studentlist, 'appointment_student', $selected_student);
        $form .= html_writer::end_tag('div');

        $form .= html_writer::start_tag('div', array('class' => "formrow", 'id' => "block_courseappointments_daterow"));
        $form .= html_writer::tag('label', get_string('date'), array('for' => "date"));
        $form .= html_writer::empty_tag('input', array('type' => "text", 'id' => "block_courseappointments_appointmentdate", 'name' => "appointment_date", 'value' => $selected_date));
        $form .= html_writer::start_tag('button', array('type' => "button", 'id' => "block_courseappointments_show", 'title' => "Show Calendar"));
	$form .= html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/calendar'), 'alt' => "Calendar"));
        $form .= html_writer::end_tag('button');
        $form .= html_writer::end_tag('div');

	$form .= html_writer::tag('div', '', array('id' => "block_courseappointments_calendarcontainer"));

        $form .= html_writer::start_tag('div', array('class' => "formrow"));
        $form .= html_writer::tag('label', get_string('time'), array('for' => "appointment_time_hours"));
        $form .= html_writer::select($hours, 'appointment_time_hours', $selected_time['hours'], array());
	$form .= ':';
       	$form .= html_writer::select($minutes, 'appointment_time_minutes', $selected_time['minutes'], array());
	$form .= html_writer::end_tag('div');

        $form .= html_writer::start_tag('div', array('class' => "formrow"));
        $form .= html_writer::tag('label', get_string('notifystudent', 'block_course_appointments'), array('for' => "appointment_notify"));
        $form .= html_writer::empty_tag('input', array('type' => "hidden", 'name' => "appointment_notify", 'value' => "0"));
        $form .= html_writer::empty_tag('input', array('type' => "checkbox", 'name' => "appointment_notify", 'value' => "1", 'checked' => $checked));
        $form .= html_writer::end_tag('div');
	
        $form .= html_writer::empty_tag('input', array('type' => "submit", 'name' => "appointment_submit", 'value' => get_string('book', 'block_course_appointments')));
        $form .= html_writer::end_tag('form');
	return $form;
    }

    function validate_form() {
        global $SESSION, $DB;
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
            if(!$this->student = $DB->get_record('user', array('id' => $studentid))) {
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
        global $USER, $COURSE, $CFG, $SESSION, $DB;
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
        $DB->insert_record('event', $appointment);
        $appointment->name = get_string('entryname', 'block_course_appointments', $names->teacher);
        $appointment->userid = $this->student->id;
        $appointment->uuid = str_replace('-', 'S', $uuid);
        $DB->insert_record('event', $appointment);
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
        $this->content->text .= html_writer::start_tag('div', array('class' => "errors"));
        foreach ($errors as $error) {
            $this->content->text .= $error.html_writer::empty_tag('br');
        }
        $this->content->text .= html_writer::end_tag('div');
    }
}
?>
