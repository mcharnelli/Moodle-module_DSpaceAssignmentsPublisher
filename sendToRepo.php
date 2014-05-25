<?php
echo("AAAAAAAAAAAAAAAAAAAAAAAAAAA");
//try{

require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/plagiarismlib.php');
try {
$swordid             = required_param('swordid',PARAM_INT);          // SWORD ID
$id             = required_param('id',PARAM_INT);          // Course module ID
$submissions    = required_param_array('submissions',PARAM_INT);// submissions selected
$cm             = get_coursemodule_from_id('assign', $id);
$assignment     = $DB->get_record("assign", array("id"=>$cm->instance));
$course         = $DB->get_record("course", array("id"=>$assignment->course));
} catch (Exception $e) {
 echo $e;
}
/*
require_login($assignment->course, false, $cm);

/// Load up the required assignment code

require($CFG->dirroot.'/mod/sword/locallib.php');


$sword_action = new sword_lib($assignment->id, $assignment, $cm, $course, $swordid);


$sword_action->sword_submissions($submissions);

/*
} catch(Exception $e)  {
  echo get_string('msg_error', 'sword');
}*/

