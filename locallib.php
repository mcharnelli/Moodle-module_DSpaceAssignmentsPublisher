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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/mod/assign/mod_form.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/grading/lib.php');
require_once($CFG->dirroot . '/mod/assign/feedbackplugin.php');
require_once($CFG->dirroot . '/mod/assign/submissionplugin.php');
require_once($CFG->dirroot . '/mod/assign/renderable.php');
require_once($CFG->dirroot . '/mod/assign/gradingtable.php');
require_once($CFG->libdir . '/eventslib.php');
require_once($CFG->libdir . '/portfolio/caller.php');
require_once($CFG->dirroot . '/mod/sword/sword_submissions_form.php');
/**
 * Internal library of functions for module sword
 *
 * All the sword specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_sword
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/mod/assign/locallib.php');

class sword_lib  
{

   private $assignmentid;
   private $assignment;
   private $cm;
   private $course;
   private $swordid;

   public function __construct($assignmentid, $assignment, $cm, $course, $swordid) {
        $this->assignmentid=$assignmentid;
        $this->assignment=$assignment;
        $this->cm=$cm;
        $this->course=$course;
        $this->swordid=$swordid;
   }

  /**
     * Download a zip file of all assignment submissions.
     *
     * @return string - If an error occurs, this will contain the error page.
     */
    protected function sword_submissions($userselected) {
        echo("Hola Emi");
        
    }
    
    
     /**
     * create xml with METS content
     * $rootin is The location of the files (without final directory)
     * $dirin is The location of the files
     * $rootout is The location to write the package out to
     * $fileout is The filename to save the package as
     */
     private function makePackage($files, $swordid, $arr, $userid,$assigid ) 
     {
        global $CFG,$DB;
         require_once('api/packager_mets_swap.php');
         
         $user=$DB->get_record('user', array('id' => $userid));
         $assignment=$DB->get_record('assignment',array('id'=> $assigid ));
         
         
        // add context metadata   
        
        $datos=array(
        "author" => $user->firstname . ' '. $user->lastname,
        "title"  => $assignment->name . ' ' . $user->lastname,
        "rootin"   => sys_get_temp_dir(), 
        "dirin"    => 'moodle',
        "rootout"  => sys_get_temp_dir().'/moodle',
	"fileout"  => basename(tempnam(sys_get_temp_dir(), 'sword_').'.zip')
	);
        
        $filesdata=array();
        foreach ($files as $file){
	    $filesdata[] = array (
	    "filename" => $file->get_filename(),
	    "mimetype" => $file->get_mimetype(),
	    );
        
        }
        
        $datos["files"]=$filesdata;
        
        
        // add default metadata
        
        $sword_metadata=$DB->get_record('sword', array('id' => $swordid));
         if (($arr!=NULL) && ($sword_metadata->subject != NULL)) {                               
           $arr[]=$sword_metadata->subject;           
           $datos["subject"]=$arr;
         } else {
           if ($arr!=NULL) {
	      $datos["subject"]=array($arr);
           }
           if ($sword_metadata->subject != NULL)      {
	    $datos["subject"]=array($sword_metadata->subject);
           }
	   
         }
         
         if($sword_metadata->rights != NULL) {
            $datos["rights"]=$sword_metadata->rights;
         }
         
         if($sword_metadata->language != NULL) {
            $datos["language"]= $sword_metadata->language;
         }
         
         if($sword_metadata->publisher != NULL) {
            $datos["publisher"]=$sword_metadata->publisher;
         }
        
                
        $this->makeMets($datos);
        
        return $datos["rootout"].'/'.$datos["fileout"];
     }
     
    /**
    * make METS package
    **/
    private function makeMets($datos) 
    {
        $packager = new PackagerMetsSwap($datos["rootin"], $datos["dirin"], $datos["rootout"], $datos["fileout"]);
        $this->loadMetadata($packager, $datos);
	$packager->create();
    }
    
    /**
    * cargar metadatos en el mets.xml
    **/
    private function loadMetadata($packager, $datos)
    {
    
        foreach($datos["files"] as $file) {
           $packager->addFile($file["filename"], $file["mimetype"]);
        }

	$packager->setTitle($datos["title"]);
	$packager->addCreator($datos["author"]);
	
	
	if (array_key_exists("subject",$datos)){
	    foreach($datos["subject"] as $subject) {
		$packager->addSubject($subject);
	    }
	}
	
	if (array_key_exists("rights",$datos)){
	    $packager->addRights($datos["rights"]);
	}
	
	if (array_key_exists("language",$datos)){
	    $packager->setLanguage($datos["language"]);
	}
	
	if (array_key_exists("publisher",$datos)){
	   $packager->setPublisher($datos["publisher"]);
	}
	
	
	
    
    }
    
     
     

     /**
     * Deposit package to repository
     * $swordid sword instance
     * $package package to deposit
     */
     private function sendToRepository($package, $submissionid, $swordid) {
     global $CFG,$DB;
     
                    $dir= sys_get_temp_dir().'mets_swap_package.zip';
                  
                    $sword=$DB->get_record('sword', array('id' => $swordid));
		    
		    // The URL of the service document
		    $url = $sword->url;
		    
		    
		    // The user (if required)
		    $user = $sword->username;
		    
		    // The password of the user (if required)
		    $pw = $sword->password;
		    

		    // Atom entry to deposit
		    $atomentry = "test-files/atom_multipart/atom";
		    
		    

		    // The test content zip file to deposit
		    $zipcontentfile = $dir;



		    // The content type of the test file
		    $contenttype = "application/zip";

		    $packageformat="http://purl.org/net/sword-types/METSDSpaceSIP";
		    
		    
		    
		    require_once($CFG->dirroot .'/mod/sword/api/swordappclient.php');
		    
		    
		    
		    $error = false;
		    try{
		        $sac = new SWORDAPPClient();
		        $dr = $sac->deposit($url, $user, $pw, '', $package, $packageformat,$contenttype, false);		   		   
		   	
			if ($dr->sac_status!=201) {  
			      $status='error';
			      $error = true;
			} else {
			      $status='send';
			      $error = false;
			}
		
		   } catch(Exception $e){		      
		      $status='error';
		      $error = true;
		   }
		   
		   
		   $previous_submission = $DB->get_record('sword_submissions',array('submission'=>$submissionid));
		   if ($previous_submission != NULL) {		      
		      $previous_submission->status = $status;
		      $DB->update_record('sword_submissions', $previous_submission);
		   } else {
		      $sword_submission=new stdClass();
		      $sword_submission->submission=$submissionid;
		      $sword_submission->status=$status;
		      $DB->insert_record('sword_submissions', $sword_submission);
		   }
		   
		   
		   
		   return $error;
     
     }
     
       /**
    * copy file to temporal directory 
    */
    private function copyFileToTemp($file) 
    {
      @mkdir(sys_get_temp_dir().'/moodle');
      $tempFile=@fopen(sys_get_temp_dir().'/moodle/'. $file->get_filename(),"wb");                    
      if ($tempFile ) {
           fwrite($tempFile,$file->get_content());
           fclose($tempFile);  
      }              
    }
}

class sword_assign extends assign {

protected function sword_submissions($userselected) {
        echo("Hola Lolo");
        
 }

public function view2($coursemodulecontext,$swordid, $action='grading') {

        $o = '';
        $mform = null;
        $notices = array();
        $nextpageparams = array();

        if (!empty($this->get_course_module()->id)) {
            $nextpageparams['id'] = $this->get_course_module()->id;
        }

        // Handle form submissions first.
        if ($action == 'savesubmission') {
            $action = 'editsubmission';
            if ($this->process_save_submission($mform, $notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'view';
            }
        } else if ($action == 'editprevioussubmission') {
            $action = 'editsubmission';
            if ($this->process_copy_previous_attempt($notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'editsubmission';
            }
        } else if ($action == 'addattempt') {
            $this->process_add_attempt(required_param('userid', PARAM_INT));
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'reverttodraft') {
            $this->process_revert_to_draft();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'unlock') {
            $this->process_unlock_submission();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'setbatchmarkingworkflowstate') {
            $this->process_set_batch_marking_workflow_state();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'setbatchmarkingallocation') {
            $this->process_set_batch_marking_allocation();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'confirmsubmit') {
            $action = 'submit';
            if ($this->process_submit_for_grading($mform)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'view';
            }
        } else if ($action == 'gradingbatchoperation') {
            $action = $this->process_grading_batch_operation($mform);
            if ($action == 'grading') {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'submitgrade') {
            if (optional_param('saveandshownext', null, PARAM_RAW)) {
                // Save and show next.
                $action = 'grade';
                if ($this->process_save_grade($mform)) {
                    $action = 'redirect';
                    $nextpageparams['action'] = 'grade';
                    $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) + 1;
                    $nextpageparams['useridlistid'] = optional_param('useridlistid', time(), PARAM_INT);
                }
            } else if (optional_param('nosaveandprevious', null, PARAM_RAW)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grade';
                $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) - 1;
                $nextpageparams['useridlistid'] = optional_param('useridlistid', time(), PARAM_INT);
            } else if (optional_param('nosaveandnext', null, PARAM_RAW)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grade';
                $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) + 1;
                $nextpageparams['useridlistid'] = optional_param('useridlistid', time(), PARAM_INT);
            } else if (optional_param('savegrade', null, PARAM_RAW)) {
                // Save changes button.
                $action = 'grade';
                if ($this->process_save_grade($mform)) {
                    $action = 'redirect';
                    $nextpageparams['action'] = 'savegradingresult';
                }
            } else {
                // Cancel button.
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'quickgrade') {
            $message = $this->process_save_quick_grades();
            $action = 'quickgradingresult';
        } else if ($action == 'saveoptions') {
            $this->process_save_grading_options();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'saveextension') {
            $action = 'grantextension';
            if ($this->process_save_extension($mform)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'revealidentitiesconfirm') {
            $this->process_reveal_identities();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        }

        $returnparams = array('rownum'=>optional_param('rownum', 0, PARAM_INT),
                              'useridlistid'=>optional_param('useridlistid', 0, PARAM_INT));
        $this->register_return_link($action, $returnparams);

        // Now show the right view page.
        if ($action == 'redirect') {
            $nextpageurl = new moodle_url('/mod/assign/view.php', $nextpageparams);
            redirect($nextpageurl);
            return;
        } else if ($action == 'savegradingresult') {
            $message = get_string('gradingchangessaved', 'assign');
            $o .= $this->view_savegrading_result($message);
        } else if ($action == 'quickgradingresult') {
            $mform = null;
            $o .= $this->view_quickgrading_result($message);
        } else if ($action == 'grade') {
            $o .= $this->view_single_grade_page($mform);
        } else if ($action == 'viewpluginassignfeedback') {
            $o .= $this->view_plugin_content('assignfeedback');
        } else if ($action == 'viewpluginassignsubmission') {
            $o .= $this->view_plugin_content('assignsubmission');
        } else if ($action == 'editsubmission') {
            $o .= $this->view_edit_submission_page($mform, $notices);
        } else if ($action == 'grading') {
            $o .= $this->view_grading_page2($coursemodulecontext, $swordid);
        } else if ($action == 'downloadall') {
            $o .= $this->download_submissions();
        } else if ($action == 'submit') {
            $o .= $this->check_submit_for_grading($mform);
        } else if ($action == 'grantextension') {
            $o .= $this->view_grant_extension($mform);
        } else if ($action == 'revealidentities') {
            $o .= $this->view_reveal_identities_confirm($mform);
        } else if ($action == 'plugingradingbatchoperation') {
            $o .= $this->view_plugin_grading_batch_operation($mform);
        } else if ($action == 'viewpluginpage') {
             $o .= $this->view_plugin_page();
        } else if ($action == 'viewcourseindex') {
             $o .= $this->view_course_index();
        } else if ($action == 'viewbatchsetmarkingworkflowstate') {
             $o .= $this->view_batch_set_workflow_state($mform);
        } else if ($action == 'viewbatchmarkingallocation') {
            $o .= $this->view_batch_markingallocation($mform);
        } else {
            $o .= $this->view_submission_page();
        }

        return $o;
    }
    
    
    /**
     * View entire grading page.
     *
     * @return string
     */
    protected function view_grading_page2($coursemodulecontext, $swordid) {
        global $CFG;

        $o = '';
        // Need submit permission to submit an assignment.
        require_capability('mod/assign:grade', $coursemodulecontext);
        require_once($CFG->dirroot . '/mod/assign/gradeform.php');

        // Only load this if it is.

        $o .= $this->view_grading_table2($coursemodulecontext, $swordid);

        $o .= $this->view_footer();

        $logmessage = get_string('viewsubmissiongradingtable', 'assign');
        $this->add_to_log('view submission grading table', $logmessage);
        return $o;
    }
    
    
     /**
     * View the grading table of all submissions for this assignment.
     *
     * @return string
     */
    protected function view_grading_table2($coursemodulecontext, $swordid) {
        global $USER, $CFG;

        // Include grading options form.
        require_once($CFG->dirroot . '/mod/assign/gradingoptionsform.php');
        require_once($CFG->dirroot . '/mod/assign/quickgradingform.php');
        require_once($CFG->dirroot . '/mod/assign/gradingbatchoperationsform.php');
        $o = '';
        $cmid = $this->get_course_module()->id;

        $links = array();
        /*if (has_capability('gradereport/grader:view', $this->get_course_context()) &&
                has_capability('moodle/grade:viewall', $this->get_course_context())) {
            $gradebookurl = '/grade/report/grader/index.php?id=' . $this->get_course()->id;
            $links[$gradebookurl] = get_string('viewgradebook', 'assign');
        }*/
        
        //@@@ agregar botón para poder seleccionar tareas para enviar al repo
        if ($this->is_any_submission_plugin_enabled() && $this->count_submissions()) {
            //$downloadurl = '/mod/assign/view.php?id=' . $cmid . '&action=sendtorepo';
           // $downloadurl= new moodle_url('/mod/assign/view.php', array('id'=> $cmid, 'action'=>'sendtorepo'));
             $downloadurl= new moodle_url("#");
           // $links[$downloadurl] = get_string('sendtorepo', 'assign');
        }
        
        
        /*if ($this->is_any_submission_plugin_enabled() && $this->count_submissions()) {
            $downloadurl = '/mod/assign/view.php?id=' . $cmid . '&action=downloadall';
            $links[$downloadurl] = get_string('downloadall', 'assign');
        }/*
      /*  if ($this->is_blind_marking() &&
                has_capability('mod/assign:revealidentities', $this->get_context())) {
            $revealidentitiesurl = '/mod/assign/view.php?id=' . $cmid . '&action=revealidentities';
            $links[$revealidentitiesurl] = get_string('revealidentities', 'assign');
        }*/
        foreach ($this->get_feedback_plugins() as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                foreach ($plugin->get_grading_actions() as $action => $description) {
                    $url = '/mod/assign/view.php' .
                           '?id=' .  $cmid .
                           '&plugin=' . $plugin->get_type() .
                           '&pluginsubtype=assignfeedback' .
                           '&action=viewpluginpage&pluginaction=' . $action;
                    $links[$url] = $description;
                }
            }
        }

        // Sort links alphabetically based on the link description.
        core_collator::asort($links);

        
        $gradingactions = new action_link($downloadurl, get_string('sendtorepo', 'sword'),null,array('onclick'=>
                                                               "enviar(". $coursemodulecontext->id . " ,".  $cmid . " ," . $swordid . ")" ));
        
        
        //echo '<input type="button" onclick= value="'.get_string('swordall', 'assignment').'" />';
        
        
        
        
        //$gradingactions = new url_select($links);
        //$gradingactions->set_label(get_string('choosegradingaction', 'assign'));

        //$gradingmanager = get_grading_manager($this->get_context(), 'mod_assign', 'submissions');

        $perpage = get_user_preferences('assign_perpage', 10);
        $filter = get_user_preferences('assign_filter', '');
        $markerfilter = get_user_preferences('assign_markerfilter', '');
        $workflowfilter = get_user_preferences('assign_workflowfilter', '');
        //$controller = $gradingmanager->get_active_controller();
        //$showquickgrading = empty($controller);
        //$quickgrading = get_user_preferences('assign_quickgrading', false);
        $showonlyactiveenrolopt = has_capability('moodle/course:viewsuspendedusers', $coursemodulecontext);

        $markingallocation = $this->get_instance()->markingallocation &&
            has_capability('mod/assign:manageallocations', $this->context);
        // Get markers to use in drop lists.
        $markingallocationoptions = array();
        if ($markingallocation) {
            $markers = get_users_by_capability($this->context, 'mod/assign:grade');
            $markingallocationoptions[''] = get_string('filternone', 'assign');
            foreach ($markers as $marker) {
                $markingallocationoptions[$marker->id] = fullname($marker);
            }
        }

        $markingworkflow = $this->get_instance()->markingworkflow;
        // Get marking states to show in form.
        $markingworkflowoptions = array();
        if ($markingworkflow) {
            $notmarked = get_string('markingworkflowstatenotmarked', 'assign');
            $markingworkflowoptions[''] = get_string('filternone', 'assign');
            $markingworkflowoptions[ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED] = $notmarked;
            $markingworkflowoptions = array_merge($markingworkflowoptions, $this->get_marking_workflow_states_for_current_user());
        }

        // Print options for changing the filter and changing the number of results per page.
        $gradingoptionsformparams = array('cm'=>$cmid,
                                          'contextid'=>$coursemodulecontext->id,
                                          'userid'=>$USER->id,
                                          'submissionsenabled'=>$this->is_any_submission_plugin_enabled(),
                                         // 'showquickgrading'=>$showquickgrading,
                                         // 'quickgrading'=>$quickgrading,
                                          'markingworkflowopt'=>$markingworkflowoptions,
                                          'markingallocationopt'=>$markingallocationoptions,
                                          'showonlyactiveenrolopt'=>$showonlyactiveenrolopt,
                                          'showonlyactiveenrol'=>$this->show_only_active_users());

        $classoptions = array('class'=>'gradingoptionsform');
        $gradingoptionsform = new mod_assign_grading_options_form(null,
                                                                  $gradingoptionsformparams,
                                                                  'post',
                                                                  '',
                                                                  $classoptions);

        $batchformparams = array('cm'=>$cmid,
                                 'submissiondrafts'=>$this->get_instance()->submissiondrafts,
                                 'duedate'=>$this->get_instance()->duedate,
                                 'attemptreopenmethod'=>$this->get_instance()->attemptreopenmethod,
                                 'feedbackplugins'=>$this->get_feedback_plugins(),
                                 'context'=>$this->get_context(),
                                 'markingworkflow'=>$markingworkflow,
                                 'markingallocation'=>$markingallocation,
                                 );
        $classoptions = array('class'=>'gradingbatchoperationsform');
        
      
       $gradingbatchoperationsform = new sword_submisison_form(null,
                                                                                   $batchformparams,
                                                                                   'post',
                                                                                   '',
                                                                                   $classoptions);
	
        $gradingoptionsdata = new stdClass();
        $gradingoptionsdata->perpage = $perpage;
        $gradingoptionsdata->filter = $filter;
        $gradingoptionsdata->markerfilter = $markerfilter;
        $gradingoptionsdata->workflowfilter = $workflowfilter;
        $gradingoptionsform->set_data($gradingoptionsdata);

       $actionformtext = $this->get_renderer()->render($gradingactions);
        $header = new assign_header($this->get_instance(),
                                    $this->get_context(),
                                    false,
                                    $this->get_course_module()->id,
                                    get_string('grading', 'assign'), 
                                    $actionformtext);
        $o .= $this->get_renderer()->render($header);

        $currenturl = $CFG->wwwroot .
                      '/mod/assign/view.php?id=' .
                      $this->get_course_module()->id .
                      '&action=grading';

        $o .= groups_print_activity_menu($this->get_course_module(), $currenturl, true);

        // Plagiarism update status apearring in the grading book.
        if (!empty($CFG->enableplagiarism)) {
            require_once($CFG->libdir . '/plagiarismlib.php');
            $o .= plagiarism_update_status($this->get_course(), $this->get_course_module());
        }

        // Load and print the table of submissions.
      /*  if ($showquickgrading && $quickgrading) {
            $gradingtable = new assign_grading_table($this, $perpage, $filter, 0, true);
            $table = $this->get_renderer()->render($gradingtable);
            $quickformparams = array('cm'=>$this->get_course_module()->id, 'gradingtable'=>$table);
            $quickgradingform = new mod_assign_quick_grading_form(null, $quickformparams);

            $o .= $this->get_renderer()->render(new assign_form('quickgradingform', $quickgradingform));
        } else {
        */    $gradingtable = new assign_grading_table($this, $perpage, $filter, 0, false);
            $o .= $this->get_renderer()->render($gradingtable);
        //}

        $currentgroup = groups_get_activity_group($this->get_course_module(), true);
        $users = array_keys($this->list_participants($currentgroup, true));
        if (count($users) != 0) {
            // If no enrolled user in a course then don't display the batch operations feature.
            $assignform = new assign_form('gradingbatchoperationsform', $gradingbatchoperationsform);
            $o .= $this->get_renderer()->render($assignform);
        }
        $assignform = new assign_form('gradingoptionsform',
                                      $gradingoptionsform,
                                      'M.mod_assign.init_grading_options');
        $o .= $this->get_renderer()->render($assignform);
        return $o;
    }
    
    /**
     * Ask the user to confirm they want to perform this batch operation
     *
     * @param moodleform $mform Set to a grading batch operations form
     * @return string - the page to view after processing these actions
     */
    protected function process_grading_batch_operation(& $mform) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/gradingbatchoperationsform.php');
        require_sesskey();

        $markingallocation = $this->get_instance()->markingallocation &&
            has_capability('mod/assign:manageallocations', $this->context);
    
        $batchformparams = array('cm'=>$this->get_course_module()->id,
                                 'submissiondrafts'=>$this->get_instance()->submissiondrafts,
                                 'duedate'=>$this->get_instance()->duedate,
                                 'attemptreopenmethod'=>$this->get_instance()->attemptreopenmethod,
                                 'feedbackplugins'=>$this->get_feedback_plugins(),
                                 'context'=>$this->get_context(),
                                 'markingworkflow'=>$this->get_instance()->markingworkflow,
                                 'markingallocation'=>$markingallocation,
                         );
        $formclasses = array('class'=>'gradingbatchoperationsform');
        $mform = new mod_assign_grading_batch_operations_form(null,
                                                              $batchformparams,
                                                              'post',
                                                              '',
                                                              $formclasses);
                                                              

        if ($data = $mform->get_data()) {
            // Get the list of users.
            $users = $data->selectedusers;
            $userlist = explode(',', $users);

            $prefix = 'plugingradingbatchoperation_';

           /* if ($data->operation == 'grantextension') {
                // Reset the form so the grant extension page will create the extension form.
                $mform = null;
                return 'grantextension';
            } else if ($data->operation == 'setmarkingworkflowstate') {
                return 'viewbatchsetmarkingworkflowstate';
            } else if ($data->operation == 'setmarkingallocation') {
                return 'viewbatchmarkingallocation';
            } else if (strpos($data->operation, $prefix) === 0) {
                $tail = substr($data->operation, strlen($prefix));
                list($plugintype, $action) = explode('_', $tail, 2);

                $plugin = $this->get_feedback_plugin_by_type($plugintype);
                if ($plugin) {
                    return 'plugingradingbatchoperation';
                }
            }*/
           // throw new Exception('Hola samigos');
           $this->sword_submissions($userlist);
            //foreach ($userlist as $userid) {
               // if ($data->operation == 'lock') {
               //throw new Exception('Hola samigos' . $userid);
              //    $this->process_send_to_repository($userid);
                 
                /*} else if ($data->operation == 'unlock') {
                    $this->process_unlock_submission($userid);
                } else if ($data->operation == 'reverttodraft') {
                    $this->process_revert_to_draft($userid);
                } else if ($data->operation == 'addattempt') {
                    if (!$this->get_instance()->teamsubmission) {
                        $this->process_add_attempt($userid);
                    }
                }*/
            //}
           if ($this->get_instance()->teamsubmission && $data->operation == 'addattempt') {
                // This needs to be handled separately so that each team submission is only re-opened one time.
                $this->process_add_attempt_group($userlist);
            }
        }

        $this->view2('grading');
    }

}