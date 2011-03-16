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
 * Processes the appointment form
 * 
 * This page recieves the form from the course appointments block, passess the data
 * to the validation method, and if all's well passess it on the the process method
 * for insertion into the database.  It then returns the user to the page displaying the block.
 * If any errors were generated during validation, processing is skipped and the errors
 * are stored in the session for display in the block.
 *
 * @package blocks
 * @subpackage courseappointments
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */ 
 
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
