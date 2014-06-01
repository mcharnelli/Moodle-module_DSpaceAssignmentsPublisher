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
 * Prints a particular instance of sword
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_sword
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace sword with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // sword instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('sword', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sword  = $DB->get_record('sword', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $sword  = $DB->get_record('sword', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $sword->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('sword', $sword->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

add_to_log($course->id, 'sword', 'view', "view.php?id={$cm->id}", $sword->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/sword/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($sword->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('sword-'.$somevar);

// Output starts here
echo $OUTPUT->header();



//code
echo $OUTPUT->heading(get_string('assignment_list', 'sword'));

$sql = 'SELECT cm.id, a.name
FROM {course_modules} cm
INNER JOIN {assign} a ON a.id = cm.instance
WHERE cm.course = a.course
AND module = (
SELECT id
FROM {modules}
WHERE name = \'assign\' )';

/*
SELECT cm.id, a.name FROM mdl_course_modules cm INNER JOIN mdl_assign a ON a.id = cm.instance 
inner join mdl_assignsubmission_file asf on asf.id = a.id WHERE cm.course = a.course AND module 
= ( SELECT id FROM mdl_modules WHERE name = 'assign' )
*/

$tareas = $DB->get_records_sql($sql, array('course'=>$course->id));
$listaTareas = array();

$table = new html_table();
$table->head = array(get_string('assignment', 'sword'));
$table->data = array();
foreach($tareas as $tarea) {
  $fila = new html_table_row();
  $url=html_writer::link(  
                         new moodle_url('/mod/sword/submissions.php', 
                                      array('id'=> $cm->id,                                             
                                            'assignment' => $tarea->id, 
                                            'sword' => $sword->id)),
                        $tarea->name);
  $fila->cells[0] = $url;
  $table->data[]=$fila;
  
  //
} 
/**
  * Assignment Module 2.2
  */
$sql = 'SELECT cm.id, a.name
FROM {course_modules} cm
INNER JOIN {assignment} a ON a.id = cm.instance
WHERE cm.course = :course
AND module = (
SELECT id
FROM {modules}
WHERE name = \'assignment\' )';
$tareas = $DB->get_records_sql($sql, array('course'=>$course->id));
foreach($tareas as $tarea) {
  $fila = new html_table_row();
  $url=html_writer::link(  
                         new moodle_url('/mod/sword/submissions22.php', 
                                      array('id'=> $cm->id,                                             
                                            'assignment' => $tarea->id)),
                        $tarea->name);
  $fila->cells[0] = $url;
  $table->data[]=$fila;
  
  //
} 
echo html_writer::table($table);

// Finish the page
echo $OUTPUT->footer();
