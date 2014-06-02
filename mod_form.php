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
 * The main sword configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage sword
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
   $PAGE->requires->js('/mod/sword/js/jquery.js', true);
   $PAGE->requires->js('/mod/sword/js/jquery-ui-1.10.4.custom.min.js', true);
   $PAGE->requires->js('/mod/sword/js/mod_form.js', true);
   $PAGE->requires->css('/mod/sword/css/blitzer/jquery-ui-1.10.4.custom.css', true);


   $PAGE->requires->css('/mod/sword/css/mod_form.css', true);
/**
 * Module instance settings form
 */
class mod_sword_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
      
        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('swordname', 'sword'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'swordname', 'sword');

        // Adding the standard "intro" and "introformat" fields
         $this->add_intro_editor(true, get_string('description', 'assignment'));

        //-------------------------------------------------------------------------------
        // Adding the rest of sword settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic
        
        $mform->addElement('header', 'repository', get_string('repository', 'sword'));
        
         $mform->addElement('html', '<div id="accordion">');
         $mform->addElement('html', '<h3>' . get_string("search_collection","sword") . "</h3>");
         
         $mform->addElement('html', '<div>');
        $mform->addElement('text', 'base_url', get_string('repositoryurl', 'sword'), array('size'=>'50'));
         $mform->setType('base_url', PARAM_CLEAN);
        
        $mform->addElement('button', 'find', get_string("search"), array('onclick' => 'getCollections(null)'));
        $mform->setType('find', PARAM_CLEAN);
       
         $mform->addElement('select', 'url_selector', get_string("selectcollection",'sword'));
        
         if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('url_selector', PARAM_TEXT);
        } else {
            $mform->setType('url_selector', PARAM_CLEAN);
        }
        $mform->addElement('html', '</div>'); 
         $mform->addElement('html', '<h3>' . get_string("url_collection","sword") . "</h3>");         
         $mform->addElement('html', '<div>');
        $mform->addElement('text','url', "Url de la colección",array("id" => "url","size"=>"64"));
        $mform->setType('url', PARAM_CLEAN);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('text', 'username', get_string('username', 'sword'), array('size'=>'64'));
        
          if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('username', PARAM_TEXT);
        } else {
            $mform->setType('username', PARAM_CLEAN);
        }
        
        $mform->addRule('username', null, 'required', null, 'client');
        $mform->addElement('password', 'password', get_string('password', 'sword'), array('size'=>'64'));
        
           if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('password', PARAM_TEXT);
        } else {
            $mform->setType('password', PARAM_CLEAN);
        }
        
        $mform->addRule('password', null, 'required', null, 'client');
        
        
        $mform->addElement('header', 'metadata', get_string('metadata', 'sword'));
        $mform->addElement('text', 'subject', get_string('subject', 'sword'), array('size'=>'64'));
        
           if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('subject', PARAM_TEXT);
        } else {
            $mform->setType('subject', PARAM_CLEAN);
        }
        
        $mform->addElement('text', 'rigths', get_string('rigths', 'sword'), array('size'=>'64'));
        
        
            if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('rigths', PARAM_TEXT);
        } else {
            $mform->setType('rigths', PARAM_CLEAN);
        }
        
        
        $mform->addElement('text', 'language', get_string('language', 'sword'), array('size'=>'64'));
        
        
          if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('language', PARAM_TEXT);
        } else {
            $mform->setType('language', PARAM_CLEAN);
        }
        
        
        $mform->addElement('text', 'publisher', get_string('publisher', 'sword'), array('size'=>'64'));
        
        
            if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('publisher', PARAM_TEXT);
        } else {
            $mform->setType('publisher', PARAM_CLEAN);
        }

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
    

    
    
}
