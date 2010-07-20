<?php
require_once('../../config.php');
require_login();
$courseid = required_param('courseid', PARAM_INT);
$coursecontext = get_context_instance(CONTEXT_COURSE, $courseid);
require_capability('block/course_appointments:book', $coursecontext);
block_load_class('course_appointments');
$block = new block_course_appointments();
$SESSION->course_appointments = array();

if (isset($_POST['appointment_submit'])) {
    if ($errors = $block->validate_form()) {        
        $SESSION->course_appointments['errors'] = $errors;
    } else {
        $block->process_form();
    }
}
redirect($CFG->wwwroot.'/course/view.php?id='.$courseid, '', 0);

?>
