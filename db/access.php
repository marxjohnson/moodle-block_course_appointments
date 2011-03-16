<?php
$capabilities = array(

    'block/course_appointments:book' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(           
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    )
);
?>
