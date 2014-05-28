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
 * Extend the base assignment class for assignments where you upload a single file
 *
 * @package   mod-assignment
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot.'/mod/sword/locallib22.php');
require_once($CFG->dirroot.'/mod/assignment/type/uploadsingle/assignment.class.php');
require_once($CFG->dirroot.'/mod/assignment/type/uploadsingle/assignment.class.php');

class sword_uploadsingle extends sword_base {
   
   private $upload_single;
   public function sword_uploadsingle($cmid='staticonly', $assignment=NULL, $cm=NULL, $course=NULL)
   {
      parent::assignment_base($cmid,$assignment,$cm,$course);
      $this->upload_single = new assignment_uploadsingle($cmid,$assignment,$cm,$course);
      
   }
   
 
   
   public function sword_submissions($submissions,$swordid)
   {    
     $sword = new sword_lib();
     $sword->sword_submissions($this,$submissions,$swordid);
   }
   public function print_student_answer($userid, $return=false) 
   {     
      return $this->upload_single->print_student_answer($userid, $return);
   }
   
   
     
}
