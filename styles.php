<?php
    require_once($CFG->dirroot.'/lib/yui/calendar/assets/skins/sam/calendar.css');
    require_once($CFG->dirroot.'/lib/yui/container/assets/skins/sam/container.css');
?>

#calcontainer {
    font-size: 9pt;
}

.yui-skin-sam .yui-calcontainer .title {
	background:url(../../lib/yui/assets/skins/sam/sprite.png) repeat-x 0 0;
}

.yui-skin-sam .yui-calcontainer .calclose {
	background:url(../../lib/yui/assets/skins/sam/sprite.png) no-repeat 0 -300px;
}

/* NAVBAR LEFT ARROW */
.yui-skin-sam .yui-calendar .calnavleft {
	background:url(../../lib/yui/assets/skins/sam/sprite.png) no-repeat 0 -450px;
}

/* NAVBAR RIGHT ARROW */
.yui-skin-sam .yui-calendar .calnavright {
	background:url(../../lib/yui/assets/skins/sam/sprite.png) no-repeat 0 -500px;
}

#appointment_date {
    width: 50%;
}

.block_course_appointments button  {
    padding:0px;
}

.block_course_appointments .errors {
    color: #f33;
}
