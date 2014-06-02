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
 * English strings for sword
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage sword
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'SWORD';
$string['sendtorepo'] = 'Send to repository';
$string['modulenameplural'] = 'swords';
$string['assignment_list']='Assignments List';
$string['assignment']='Tarea';
$string['msg_error']='Fail deposit';
$string['msg_send']='Successful deposit';
$string['modulename_help'] = 'El módulo SWORD es una extensión del módulo de Tareas que permite a un profesor exportar las entregas realizadas por los alumnos a un repositorio digital DSpace. Dichas entregas pertenecen a cada una de las tareas creadas por el docente a través del módulo Tareas.
Se debe crear una actividad SWORD por cada repositorio que quiera asociar. El docente debe establecer la colección donde dichos recursos se publicarán y un usuario y contraseña para poder realizar el depósito. También podrá definir valores por defecto para algunos metadatos en el estándar Dublin Core simplificado.
A su vez, el alumno puede proveer metadatos subject (palabras claves) para completar su recurso, a través de un archivo .txt con una palabra clave por línea.
Éste módulo recolecta, formatea e incorpora metadatos de forma automática a través del contexto donde su ubica la tarea y por medio de metadatos que el docente puede brindar.
Funciona para los módulos Tareas y Tareas 2.2, y publica todo tipo de entrega';
$string['nosend']='Not send';
$string['send']='Send';
$string['error']='Error to deposit'; 
$string['repositoryurl'] = "Enter the repository's url";
$string['repository']='Repository';
$string['collection']= 'collection';
$string['username']='Username';
$string['password']='Password';
$string['metadata']='Default metadata values';
$string['subject']='Subject';
$string['rights']='Rights';
$string['language']='Language';
$string['publisher']='Publisher';
$string['swordname'] = 'SWORD name';
$string['msg-repository']="Complete de url with the sword ubication and handle's destine collection";
$string['swordname_help'] = 'This is the content of the help tooltip associated with the swordname field. Markdown syntax is supported.';
$string['sword'] = 'sword';
$string['pluginadministration'] = 'sword administration';
$string['pluginname'] = 'sword';
$string['selectcollection'] = 'Select a collection';
$string['publish_status']   = "Publish status";
$string['search_collection'] = "Search the collection";
$string['url_collection'] = "Enter the collection's URL";
$string['non_selected'] = "You have not selected any submission";
$string['cannot_get_collections'] = "Failed to get the collections";