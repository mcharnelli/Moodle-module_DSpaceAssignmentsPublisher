<?php
require_once("../../config.php");
require_once("locallib22.php");
require_once($CFG->libdir.'/plagiarismlib.php');

$PAGE->requires->js('/mod/sword/js/sword22.js', true);
$PAGE->requires->js('/mod/sword/js/jquery.js', true);


$id   = optional_param('id', 0, PARAM_INT);          // Course module ID
$assigment    = optional_param('assignment', 0, PARAM_INT);           // Assignment ID
$mode = optional_param('mode', 'all', PARAM_ALPHA);  // What mode are we in?


$url = new moodle_url('/mod/sword/submissions.php');
if ($id) {
    if (! $cm = get_coursemodule_from_id('assignment', $assigment)) {
        print_error('invalidcoursemodule');
    }

    if (! $assignment = $DB->get_record("assignment", array("id"=>$cm->instance))) {
        print_error('invalidid', 'assignment');
    }

    if (! $course = $DB->get_record("course", array("id"=>$assignment->course))) {
        print_error('coursemisconf', 'assignment');
    }
    $url->param('id', $id);
} else {
    if (!$assignment = $DB->get_record("assignment", array("id"=>$a))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id"=>$assignment->course))) {
        print_error('coursemisconf', 'assignment');
    }
    if (! $cm = get_coursemodule_from_instance("assignment", $assignment->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    $url->param('a', $a);
}

if ($mode !== 'all') {
    $url->param('mode', $mode);
}
$PAGE->set_url($url);
require_login($course->id, false, $cm);

require_capability('mod/assignment:grade',context_module::instance($cm->id) );

$PAGE->requires->js('/mod/assignment/assignment.js');

/// Load up the required assignment code
require($CFG->dirroot.'/mod/sword/type/'.$assignment->assignmenttype.'/assignment.class.php');
$assignmentclass = 'sword_'.$assignment->assignmenttype;
$assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);
$assignmentinstance->set_sword_ID($id);
$assignmentinstance->display_submissions($mode);

   