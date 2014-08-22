<?php
try{
require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/plagiarismlib.php');


$course_id      = required_param('id',PARAM_INT);          // Course module ID
$sword_cm_id    = required_param('sword_cm_id',PARAM_INT);          // Course module ID
$submissions    = required_param_array('submissions',PARAM_INT);// submissions selected
$cm             = get_coursemodule_from_id('assignment', $course_id);
$assignment     = $DB->get_record("assignment", array("id"=>$cm->instance));
$course         = $DB->get_record("course", array("id"=>$assignment->course));

require_login($assignment->course, false, $cm);

/// Load up the required assignment code

require($CFG->dirroot.'/mod/sword/type/'.$assignment->assignmenttype.'/assignment.class.php');
$assignmentclass = 'sword_'.$assignment->assignmenttype;
$assignmentinstance = new $assignmentclass($assignment->id, $assignment, $cm, $course);


$assignmentinstance->sword_submissions($submissions,$sword_cm_id);

} catch(Exception $e)  {
  echo get_string('msg_error', 'sword');
}
