<?php
require_once($CFG->libdir.'/formslib.php');

class appointment_form extends moodleform {
    public function definition() {
        global $COURSE;
        $mform =& $this->_form;

        $studentrole = get_record('role', 'shortname', 'student');
        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        $studentassignments = get_users_from_role_on_context($studentrole, $coursecontext);
        $studentlist = array();

        foreach($studentassignments as $studentassignment) {
            $studentrecord = get_record('user', 'id', $studentassignment->userid);
            $studentlist[$studentrecord->id] = fullname($studentrecord);
        }

        $mform->addElement('select', 'select', $studentrole->name, $studentlist);
        $mform->addElement('date_selector', 'datetime', get_string('date'));
        $mform->addElement('advcheckbox', 'notifystudent', get_string('notifystudent', 'block_course_appointments'));
        $mform->addElement('submit', 'book', get_string('book', 'block_course_appointments'));
        
    }
}

?>
