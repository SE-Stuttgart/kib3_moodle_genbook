<?php
// This file is part of the development project WS 21/22 by the university of Stuttgart

/**
 * Version details
 *
 * @package    block_definitions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/formslib.php");

class generate_book_form extends moodleform
{

    //Add elements to form
    public function definition()
    {
        $mform = $this->_form;
        //drop down lists
        $dummy_options =  array('FI' => 'FI', 'S' => 'S', 'M' => 'M');
        $mform->addElement('select', 'type', get_string('select_course', 'genbook'), $dummy_options);
        $mform->addElement('select', 'type', get_string('select_section', 'genbook'), $dummy_options);

        $mform->addElement('filemanager', 'attachments', get_string('attachment', 'moodle'), null,
                    array('subdirs' => 0, 'maxbytes' => 10485760, 'areamaxbytes' => 10485760, 'maxfiles' => 50,
                          'accepted_types' => array('.jpg','.png'), 'return_types'=> FILE_INTERNAL | FILE_EXTERNAL));
        
        $mform->addElement('filepicker', 'configuration', get_string('file'), null,
                   array('maxbytes' => 10485760, 'accepted_types' => '*'));

        // Submit and cancel button
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('delete', 'block_definitions'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }
}
