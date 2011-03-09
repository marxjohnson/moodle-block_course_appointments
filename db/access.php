<?php
$capabilities = array(

    'block/course_appointments:book' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(           
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    )
);
?>
