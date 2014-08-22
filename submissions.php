<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/sword/locallib.php');

$PAGE->requires->js('/mod/sword/js/sword.js', true);
$PAGE->requires->js('/mod/sword/js/jquery.js', true);
$PAGE->requires->css('/mod/sword/css/estilo.css', true);


$id = required_param('id', PARAM_INT); // Course module ID
$assignment    = required_param('assignment', PARAM_INT);           // Assignment ID
$cm_sword = get_coursemodule_from_id('sword', $id, 0, false, MUST_EXIST);

$urlparams = array('id' => $id,                  
                  'assignment' => $assignment);
                  
$url = new moodle_url('/mod/sword/submissions.php', $urlparams);


$cm = get_coursemodule_from_id('assign', $assignment, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url($url);

$context = context_module::instance($cm->id);

require_capability('mod/assign:view', $context);

$sword_assign = new sword_assign($context,$cm,$course, $cm_sword);
$completion=new completion_info($course);
$completion->set_module_viewed($cm);

// Get the assign class to
// render the page.
echo $sword_assign->view();
echo html_writer::tag('div', '', array('class'=>'modal')); 
   