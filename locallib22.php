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
 * Library of interface functions and constants for module sword
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the sword specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod
 * @subpackage sword
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('NEWMODULE_ULTIMATE_ANSWER', 42);









require_once($CFG->dirroot.'/mod/assignment/lib.php');

class sword_lib  
{


   /**
     * Deposit assignment submissions in digital repository
     */
     public function sword_submissions($assignment_type,$submissions_id, $swordid) {
        global $CFG,$DB;
        require_once($CFG->libdir.'/filelib.php');
	
        
        $submissions=$DB->get_records("assignment_submissions", array("assignment"=>$assignment_type->assignment->id));
         $select_submissions = array();
        foreach ($submissions as $submission) {
           if (in_array($submission->id,$submissions_id)) { 
              $select_submissions[]=$submission;
           }
        }
        
        
        
        $context = context_module::instance($assignment_type->assignment->id);
	
        $fs = get_file_storage();

        $groupmode = groups_get_activity_groupmode($assignment_type->cm);
        $groupid = 0;   // All users
        $groupname = '';
        
        if ($groupmode) {
            $groupid = groups_get_activity_group($$assignment_type->cm, true);
            $groupname = groups_get_group_name($groupid).'-';
        }
        $error = false;
        foreach ($select_submissions as $submission) {
            
            $a_userid = $submission->userid; //get userid
            if ((groups_is_member($groupid,$a_userid)or !$groupmode or !$groupid)) {   
                
                
                $files = $fs->get_area_files($assignment_type->context->id, 'mod_assignment', 'submission', $submission->id, "timemodified", false);
                $arr=NULL;
                foreach ($files as $file) {
                    if($file) {
                      $filetitle=$file->get_filename();
                      $newstring = substr($filetitle, -3);
                      if($newstring=="txt"){
                         $contents = $file->get_content();
                         $arr = explode("\n", $contents);                
                      } 
                      
                      $this->copyFileToTemp($file);
                      $paquete = $this->makePackage($files, $swordid, $arr, $a_userid, $assignment_type->assignment->id);
                      
                      $resultado  = $this->sendToRepository($paquete,$submission->id, $swordid);;
                      $error = $error ||  $resultado;
                    }
                    

                }
                
            }
        } 
        
        if ($error==true) {
	  echo get_string('msg_error', 'sword');
        } else {
	  echo get_string('msg_send', 'sword');
        }
       
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
		        print_r($dr);
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
class sword_base extends assignment_base 
{
  protected  $swordid;
   public function set_sword_ID($id)
   {
    $this->swordid=$id;
   }
    /**
     *  Display all the submissions ready for grading
     *
     * @global object
     * @global object
     * @global object
     * @global object
     * @param string $message
     * @return bool|void
     */
    function display_submissions( $message='') {
    
        global $CFG, $DB, $USER, $DB, $OUTPUT, $PAGE;
        require_once($CFG->libdir.'/gradelib.php');

        /* first we check to see if the form has just been submitted
         * to request user_preference updates
         */

       $filters = array(self::FILTER_ALL             => get_string('all'),
                        self::FILTER_REQUIRE_GRADING => get_string('requiregrading', 'assignment'));

        $updatepref = optional_param('updatepref', 0, PARAM_BOOL);
        if ($updatepref) {
            $perpage = optional_param('perpage', 10, PARAM_INT);
            $perpage = ($perpage <= 0) ? 10 : $perpage ;
            $filter = optional_param('filter', 0, PARAM_INT);
            set_user_preference('assignment_perpage', $perpage);
            set_user_preference('assignment_quickgrade', optional_param('quickgrade', 0, PARAM_BOOL));
            set_user_preference('assignment_filter', $filter);
        }

        /* next we get perpage and quickgrade (allow quick grade) params
         * from database
         */
        $perpage    = get_user_preferences('assignment_perpage', 10);
        $quickgrade = get_user_preferences('assignment_quickgrade', 0) && $this->quickgrade_mode_allowed();
        $filter = get_user_preferences('assignment_filter', 0);
        $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id);

        if (!empty($CFG->enableoutcomes) and !empty($grading_info->outcomes)) {
            $uses_outcomes = true;
        } else {
            $uses_outcomes = false;
        }

        $page    = optional_param('page', 0, PARAM_INT);
        $strsaveallfeedback = get_string('saveallfeedback', 'assignment');

    /// Some shortcuts to make the code read better

        $course     = $this->course;
        $assignment = $this->assignment;
        $cm         = $this->cm;
        $hassubmission = false;

        // reset filter to all for offline assignment only.
        if ($assignment->assignmenttype == 'offline') {
            if ($filter == self::FILTER_SUBMITTED) {
                $filter = self::FILTER_ALL;
            }
        } else {
            $filters[self::FILTER_SUBMITTED] = get_string('submitted', 'assignment');
        }

        $tabindex = 1; //tabindex for quick grading tabbing; Not working for dropdowns yet
        add_to_log($course->id, 'assignment', 'view submission', 'submissions.php?id='.$this->cm->id, $this->assignment->id, $this->cm->id);
        $PAGE->requires->js('/mod/sword/js/jquery.js', true);
        $PAGE->requires->js('/mod/sword/js/sword22.js', true);
        $PAGE->requires->css('/mod/sword/css/estilo.css', true);
        
               // array(array('aparam'=>'paramvalue')));
        
        $PAGE->set_title(format_string($this->assignment->name,true));
        $PAGE->set_heading($this->course->fullname);
        echo $OUTPUT->header();

        echo '<div class="usersubmissions">';

        //hook to allow plagiarism plugins to update status/print links.
        echo plagiarism_update_status($this->course, $this->cm);

        $course_context = get_context_instance(CONTEXT_COURSE, $course->id);
        if (has_capability('gradereport/grader:view', $course_context) && has_capability('moodle/grade:viewall', $course_context)) {
            echo '<div class="allcoursegrades"><a href="' . $CFG->wwwroot . '/grade/report/grader/index.php?id=' . $course->id . '">'
                . get_string('seeallcoursegrades', 'grades') . '</a></div>';
        }

        if (!empty($message)) {
            echo $message;   // display messages here if any
        }

        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    /// Check to see if groups aredisplay_submissions being used in this assignment

        /// find out current groups mode
        $groupmode = groups_get_activity_groupmode($cm);
        $currentgroup = groups_get_activity_group($cm, true);
        groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/assignment/submissions.php?id=' . $this->cm->id);

        /// Print quickgrade form around the table
        if ($quickgrade) {
            $formattrs = array();
            $formattrs['action'] = new moodle_url('/mod/assignment/submissions.php');
            $formattrs['id'] = 'fastg';
            $formattrs['method'] = 'post';

            echo html_writer::start_tag('form', $formattrs);
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id',      'value'=> $this->cm->id));
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'mode',    'value'=> 'fastgrade'));
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'page',    'value'=> $page));
            echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=> sesskey()));
        }

        /// Get all ppl that are allowed to submit assignments
        list($esql, $params) = get_enrolled_sql($context, 'mod/assignment:submit', $currentgroup);

        if ($filter == self::FILTER_ALL) {
            $sql = "SELECT u.id FROM {user} u ".
                   "LEFT JOIN ($esql) eu ON eu.id=u.id ".
                   "WHERE u.deleted = 0 AND eu.id=u.id ";
        } else {
            $wherefilter = ' AND s.assignment = '. $this->assignment->id;
            $assignmentsubmission = "LEFT JOIN {assignment_submissions} s ON (u.id = s.userid) ";
            if($filter == self::FILTER_SUBMITTED) {
                $wherefilter .= ' AND s.timemodified > 0 ';
            } else if($filter == self::FILTER_REQUIRE_GRADING && $assignment->assignmenttype != 'offline') {
                $wherefilter .= ' AND s.timemarked < s.timemodified ';
            } else { // require grading for offline assignment
                $assignmentsubmission = "";
                $wherefilter = "";
            }

            $sql = "SELECT u.id FROM {user} u ".
                   "LEFT JOIN ($esql) eu ON eu.id=u.id ".
                   $assignmentsubmission.
                   "WHERE u.deleted = 0 AND eu.id=u.id ".
                   $wherefilter;
        }

        $users = $DB->get_records_sql($sql, $params);
        if (!empty($users)) {
            if($assignment->assignmenttype == 'offline' && $filter == self::FILTER_REQUIRE_GRADING) {
                //remove users who has submitted their assignment
                foreach ($this->get_submissions() as $submission) {
                    if (array_key_exists($submission->userid, $users)) {
                        unset($users[$submission->userid]);
                    }
                }
            }
            $users = array_keys($users);
        }

        // if groupmembersonly used, remove users who are not in any group
        if ($users and !empty($CFG->enablegroupmembersonly) and $cm->groupmembersonly) {
            if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
                $users = array_intersect($users, array_keys($groupingusers));
            }
        }

        $extrafields = get_extra_user_fields($context);
        //select row
        $tablecolumns =  array_merge(array('state'),array('select'),array('picture', 'fullname'), $extrafields,
                array('grade', 'submissioncomment', 'timemodified', 'timemarked', 'status', 'finalgrade'));
        if ($uses_outcomes) {
            $tablecolumns[] = 'outcome'; // no sorting based on outcomes column
        }

        $extrafieldnames = array();
        foreach ($extrafields as $field) {
            $extrafieldnames[] = get_user_field_name($field);
        }
        $tableheaders = array_merge(
               array(get_string('status')),
               array(get_string('select')),
                array('', get_string('fullnameuser')),
                $extrafieldnames,
                array(
                    get_string('grade'),
                    get_string('comment', 'assignment'),
                    get_string('lastmodified').' ('.get_string('submission', 'assignment').')',
                    get_string('lastmodified').' ('.get_string('grade').')',
                    get_string('status'),
                    get_string('finalgrade', 'grades'),
                ));
        if ($uses_outcomes) {
            $tableheaders[] = get_string('outcome', 'grades');
        }

        require_once($CFG->libdir.'/tablelib.php');
        echo '<form>';
        $table = new flexible_table('mod-assignment-submissions');

        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($CFG->wwwroot.'/mod/assignment/submissions.php?id='.$this->cm->id.'&amp;currentgroup='.$currentgroup);

        $table->sortable(true, 'lastname');//sorted by lastname by default
        $table->collapsible(true);
        $table->initialbars(true);

        $table->column_suppress('picture');
        $table->column_suppress('fullname');
 
        
        $table->column_class('state', 'state');
        $table->column_class('select', 'select');
        $table->column_class('picture', 'picture');
        $table->column_class('fullname', 'fullname');
        foreach ($extrafields as $field) {
            $table->column_class($field, $field);
        }
        $table->column_class('grade', 'grade');
        $table->column_class('submissioncomment', 'comment');
        $table->column_class('timemodified', 'timemodified');
        $table->column_class('timemarked', 'timemarked');
        $table->column_class('status', 'status');
        $table->column_class('finalgrade', 'finalgrade');
        if ($uses_outcomes) {
            $table->column_class('outcome', 'outcome');
        }

        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'attempts');
        $table->set_attribute('class', 'submissions');
        $table->set_attribute('width', '100%');

        $table->no_sorting('finalgrade');
        $table->no_sorting('outcome');

        // Start working -- this is necessary as soon as the niceties are over
        $table->setup();

        /// Construct the SQL
        list($where, $params) = $table->get_sql_where();
        if ($where) {
            $where .= ' AND ';
        }

        if ($filter == self::FILTER_SUBMITTED) {
           $where .= 's.timemodified > 0 AND ';
        } else if($filter == self::FILTER_REQUIRE_GRADING) {
            $where = '';
            if ($assignment->assignmenttype != 'offline') {
               $where .= 's.timemarked < s.timemodified AND ';
            }
        }

        if ($sort = $table->get_sql_sort()) {
            $sort = ' ORDER BY '.$sort;
        }

        $ufields = user_picture::fields('u', $extrafields);
        if (!empty($users)) {
            $select = "SELECT $ufields,
                              s.id AS submissionid, s.grade, s.submissioncomment,
                              s.timemodified, s.timemarked,
                              CASE WHEN s.timemarked > 0 AND s.timemarked >= s.timemodified THEN 1
                                   ELSE 0 END AS status ";

            $sql = 'FROM {user} u '.
                   'LEFT JOIN {assignment_submissions} s ON u.id = s.userid
                    AND s.assignment = '.$this->assignment->id.' '.
                   'WHERE '.$where.'u.id IN ('.implode(',',$users).') ';

            $ausers = $DB->get_records_sql($select.$sql.$sort, $params, $table->get_page_start(), $table->get_page_size());

            $table->pagesize($perpage, count($users));

            ///offset used to calculate index of student in that particular query, needed for the pop up to know who's next
            $offset = $page * $perpage;
            $strupdate = get_string('update');
            $strgrade  = get_string('grade');
            $strview  = get_string('view');
            $grademenu = make_grades_menu($this->assignment->grade);

            if ($ausers !== false) {
                $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, array_keys($ausers));
                $endposition = $offset + $perpage;
                $currentposition = 0;
                foreach ($ausers as $auser) {
                    if ($currentposition == $offset && $offset < $endposition) {
                        $rowclass = null;
                        $final_grade = $grading_info->items[0]->grades[$auser->id];
                        $grademax = $grading_info->items[0]->grademax;
                        $final_grade->formatted_grade = round($final_grade->grade,2) .' / ' . round($grademax,2);
                        $locked_overridden = 'locked';
                        if ($final_grade->overridden) {
                            $locked_overridden = 'overridden';
                        }

                        // TODO add here code if advanced grading grade must be reviewed => $auser->status=0
                        
                        $picture = $OUTPUT->user_picture($auser);

                        if (empty($auser->submissionid)) {
                            $auser->grade = -1; //no submission yet
                        }
                        $selectAssig=NULL; 
                        if (!empty($auser->submissionid)) {
                            $hassubmission = true;
                            
                            $selectAssig= $auser->submissionid;
                        ///Prints student answer and student modified date
                        ///attach file or print link to student answer, depending on the type of the assignment.
                        ///Refer to print_student_answer in inherited classes.
                            if ($auser->timemodified > 0) {
                                $studentmodifiedcontent = $this->print_student_answer($auser->id)
                                        . userdate($auser->timemodified);
                                if ($assignment->timedue && $auser->timemodified > $assignment->timedue && $this->supports_lateness()) {
                                    $studentmodifiedcontent .= $this->display_lateness($auser->timemodified);
                                    $rowclass = 'late';
                                }
                            } else {
                                $studentmodifiedcontent = '&nbsp;';
                            }
                            $studentmodified = html_writer::tag('div', $studentmodifiedcontent, array('id' => 'ts' . $auser->id));
                        ///Print grade, dropdown or text
                            if ($auser->timemarked > 0) {
                                $teachermodified = '<div id="tt'.$auser->id.'">'.userdate($auser->timemarked).'</div>';

                                if ($final_grade->locked or $final_grade->overridden) {
                                    $grade = '<div id="g'.$auser->id.'" class="'. $locked_overridden .'">'.$final_grade->formatted_grade.'</div>';
                                } else if ($quickgrade) {
                                    $attributes = array();
                                    $attributes['tabindex'] = $tabindex++;
                                    $menu = html_writer::label(get_string('assignment:grade', 'assignment'), 'menumenu'. $auser->id, false, array('class' => 'accesshide'));
                                    $menu .= html_writer::select(make_grades_menu($this->assignment->grade), 'menu['.$auser->id.']', $auser->grade, array(-1=>get_string('nograde')), $attributes);
                                    $grade = '<div id="g'.$auser->id.'">'. $menu .'</div>';
                                } else {
                                    $grade = '<div id="g'.$auser->id.'">'.$this->display_grade($auser->grade).'</div>';
                                }

                            } else {
                                $teachermodified = '<div id="tt'.$auser->id.'">&nbsp;</div>';
                                if ($final_grade->locked or $final_grade->overridden) {
                                    $grade = '<div id="g'.$auser->id.'" class="'. $locked_overridden .'">'.$final_grade->formatted_grade.'</div>';
                                } else if ($quickgrade) {
                                    $attributes = array();
                                    $attributes['tabindex'] = $tabindex++;
                                    $menu = html_writer::label(get_string('assignment:grade', 'assignment'), 'menumenu'. $auser->id, false, array('class' => 'accesshide'));
                                    $menu .= html_writer::select(make_grades_menu($this->assignment->grade), 'menu['.$auser->id.']', $auser->grade, array(-1=>get_string('nograde')), $attributes);
                                    $grade = '<div id="g'.$auser->id.'">'.$menu.'</div>';
                                } else {
                                    $grade = '<div id="g'.$auser->id.'">'.$this->display_grade($auser->grade).'</div>';
                                }
                            }
                        ///Print Comment
                            if ($final_grade->locked or $final_grade->overridden) {
                                $comment = '<div id="com'.$auser->id.'">'.shorten_text(strip_tags($final_grade->str_feedback),15).'</div>';

                            } else if ($quickgrade) {
                                $comment = '<div id="com'.$auser->id.'">'
                                         . '<textarea tabindex="'.$tabindex++.'" name="submissioncomment['.$auser->id.']" id="submissioncomment'
                                         . $auser->id.'" rows="2" cols="20">'.($auser->submissioncomment).'</textarea></div>';
                            } else {
                                $comment = '<div id="com'.$auser->id.'">'.shorten_text(strip_tags($auser->submissioncomment),15).'</div>';
                            }
                        } else {
                            $studentmodified = '<div id="ts'.$auser->id.'">&nbsp;</div>';
                            $teachermodified = '<div id="tt'.$auser->id.'">&nbsp;</div>';
                            $status          = '<div id="st'.$auser->id.'">&nbsp;</div>';

                            if ($final_grade->locked or $final_grade->overridden) {
                                $grade = '<div id="g'.$auser->id.'">'.$final_grade->formatted_grade . '</div>';
                                $hassubmission = true;
                            } else if ($quickgrade) {   // allow editing
                                $attributes = array();
                                $attributes['tabindex'] = $tabindex++;
                                $menu = html_writer::label(get_string('assignment:grade', 'assignment'), 'menumenu'. $auser->id, false, array('class' => 'accesshide'));
                                $menu .= html_writer::select(make_grades_menu($this->assignment->grade), 'menu['.$auser->id.']', $auser->grade, array(-1=>get_string('nograde')), $attributes);
                                $grade = '<div id="g'.$auser->id.'">'.$menu.'</div>';
                                $hassubmission = true;
                            } else {
                                $grade = '<div id="g'.$auser->id.'">-</div>';
                            }

                            if ($final_grade->locked or $final_grade->overridden) {
                                $comment = '<div id="com'.$auser->id.'">'.$final_grade->str_feedback.'</div>';
                            } else if ($quickgrade) {
                                $comment = '<div id="com'.$auser->id.'">'
                                         . '<textarea tabindex="'.$tabindex++.'" name="submissioncomment['.$auser->id.']" id="submissioncomment'
                                         . $auser->id.'" rows="2" cols="20">'.($auser->submissioncomment).'</textarea></div>';
                            } else {
                                $comment = '<div id="com'.$auser->id.'">&nbsp;</div>';
                            }
                        }

                        if (empty($auser->status)) { /// Confirm we have exclusively 0 or 1
                            $auser->status = 0;
                        } else {
                            $auser->status = 1;
                        }

                        $buttontext = ($auser->status == 1) ? $strupdate : $strgrade;
                        if ($final_grade->locked or $final_grade->overridden) {
                            $buttontext = $strview;
                        }

                        ///No more buttons, we use popups ;-).
                        $popup_url = '/mod/assignment/submissions.php?id='.$this->cm->id
                                   . '&amp;userid='.$auser->id.'&amp;mode=single'.'&amp;filter='.$filter.'&amp;offset='.$offset++;

                        $button = $OUTPUT->action_link($popup_url, $buttontext);

                        $status  = '<div id="up'.$auser->id.'" class="s'.$auser->status.'">'.$button.'</div>';

                        $finalgrade = '<span id="finalgrade_'.$auser->id.'">'.$final_grade->str_grade.'</span>';

                        $outcomes = '';

                        if ($uses_outcomes) {

                            foreach($grading_info->outcomes as $n=>$outcome) {
                                $outcomes .= '<div class="outcome"><label for="'. 'outcome_'.$n.'_'.$auser->id .'">'.$outcome->name.'</label>';
                                $options = make_grades_menu(-$outcome->scaleid);

                                if ($outcome->grades[$auser->id]->locked or !$quickgrade) {
                                    $options[0] = get_string('nooutcome', 'grades');
                                    $outcomes .= ': <span id="outcome_'.$n.'_'.$auser->id.'">'.$options[$outcome->grades[$auser->id]->grade].'</span>';
                                } else {
                                    $attributes = array();
                                    $attributes['tabindex'] = $tabindex++;
                                    $attributes['id'] = 'outcome_'.$n.'_'.$auser->id;
                                    $outcomes .= ' '.html_writer::select($options, 'outcome_'.$n.'['.$auser->id.']', $outcome->grades[$auser->id]->grade, array(0=>get_string('nooutcome', 'grades')), $attributes);
                                }
                                $outcomes .= '</div>';
                            }
                        }

                        $userlink = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $auser->id . '&amp;course=' . $course->id . '">' . fullname($auser, has_capability('moodle/site:viewfullnames', $this->context)) . '</a>';
                        $extradata = array();
                        foreach ($extrafields as $field) {
                            $extradata[] = $auser->{$field};
                        }
                        
                        $check='';
                        if ($selectAssig == NULL ) {
                          $check=html_writer::checkbox('submission_selected',$selectAssig,false,'',array('disabled'=>'disabled'));

                        } else {
                          $check=html_writer::checkbox('submission_selected',$selectAssig,false,NULL,array('class'=>"usercheckbox"));
                        }
                        
                        
                        $sword_submission=$DB->get_record('sword_submissions', array('submission' => $selectAssig));
                       
                        if ($sword_submission!=NULL)  { 
                           
                           $estado=get_string($sword_submission->status, 'sword');
                        }
                        else {
                           $estado=get_string('nosend', 'sword');
                        }
                        
                        $row = array_merge(array($estado),array($check),array($picture, $userlink), $extradata,
                                array($grade, $comment, $studentmodified, $teachermodified,
                                $status, $finalgrade));
                        if ($uses_outcomes) {
                            $row[] = $outcomes;
                        }
                        $table->add_data($row, $rowclass);
                         }
                    $currentposition++;
                }
               
		echo html_writer::empty_tag('input',
					    array('type'  => 'hidden',
						  'name'  => 'id',
						  'value' => $this->cm->id)
			                   );
                
               
                
                 echo '<input type="button" onclick="enviar('.$this->cm->id.' ,'. $this->cm->instance.' ,'. $this->swordid.')"  value="'.get_string('swordall', 'assignment').'" />';
                                  
                 
               
  
                
                echo '</form>';
                
                
                $table->print_html();  /// Print the whole table
            } else {
                if ($filter == self::FILTER_SUBMITTED) {
                    echo html_writer::tag('div', get_string('nosubmisson', 'assignment'), array('class'=>'nosubmisson'));
                } else if ($filter == self::FILTER_REQUIRE_GRADING) {
                    echo html_writer::tag('div', get_string('norequiregrading', 'assignment'), array('class'=>'norequiregrading'));
                }
            }
        }

        /// Print quickgrade form around the table
        if ($quickgrade && $table->started_output && !empty($users)){
            $mailinfopref = false;
            if (get_user_preferences('assignment_mailinfo', 1)) {
                $mailinfopref = true;
            }
            $emailnotification =  html_writer::checkbox('mailinfo', 1, $mailinfopref, get_string('enablenotification','assignment'));

            $emailnotification .= $OUTPUT->help_icon('enablenotification', 'assignment');
            echo html_writer::tag('div', $emailnotification, array('class'=>'emailnotification'));

            $savefeedback = html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'fastg', 'value'=>get_string('saveallfeedback', 'assignment')));
            echo html_writer::tag('div', $savefeedback, array('class'=>'fastgbutton'));

            echo html_writer::end_tag('form');
        } else if ($quickgrade) {
            echo html_writer::end_tag('form');
        }

        echo '</div>';
        echo '<div class="modal"><!-- Place at bottom of page --></div>';

        echo $OUTPUT->footer();
    }


}
