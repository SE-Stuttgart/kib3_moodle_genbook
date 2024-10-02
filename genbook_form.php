<?php
// This file is part of the development project WS 21/22 by the university of Stuttgart

/**
 * Version details
 *
 * @package    booktool_genbook
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class generate_book_form extends moodleform
{

    //Add elements to form
    public function definition()
    {
        $mform = $this->_form;

        $returnurl = $this->_customdata['returnurl'];
        $mform->addElement('hidden', 'returnurl', null);
        $mform->setType('returnurl', PARAM_LOCALURL);
        $mform->setConstant('returnurl', $returnurl);
        
        // Course selection
        $course_id = $this->_customdata['cid'];
        $courses_shown = $this->select_courses($course_id);
        $mform->addElement('select', 'cid', get_string('select_course', 'booktool_genbook'), $courses_shown);

        // Section selection
        $section_id = $this->_customdata['sid'];
        if($course_id){
            $sections_shown = $this->select_section($course_id, $section_id);
        } else {
            $sections_shown = array();
        }
        $mform->addElement('select', 'sid', get_string('select_section', 'booktool_genbook'), $sections_shown);

        // Name input field
        $mform->addElement('text', 'bookname', get_string('bookname_input', 'booktool_genbook'));
        $mform->setType('bookname', PARAM_NOTAGS);
        $mform->addRule('bookname', get_string('empty_name', 'booktool_genbook'), 'required', null, 'client');

        $mform->addElement('filemanager', 'attachments', get_string('attachment', 'repository'), null,
            array('subdirs' => 0, 'accepted_types' => array('image'))
        );
        $mform->addHelpButton('attachments', 'attachment', 'booktool_genbook');
        
        $mform->addElement('filepicker', 'configuration', get_string('config_file', 'booktool_genbook'), null,
                   array('maxbytes' => 10485760, 'accepted_types' => '*'));
        $mform->addHelpButton('configuration', 'config_file', 'booktool_genbook');

        // Submit and cancel button
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('create', 'booktool_genbook'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }

    /**
     * Returns a course with the given id, else selection of courses
     * @param int $id Given course id
     * @return array list of courses which user can choose from
     */
    private function select_courses(int $id){
        global $DB;
        if ($id){
            try{
                $courses = array($DB->get_record('course', array('id'=>$id), '*', MUST_EXIST));
            } catch (Exception $e){
                $courses = $DB->get_records('course');
            }
        } else {
            $courses = $DB->get_records('course');
        }
        $courses_shown = array();
        foreach ($courses as $course) {
            $courses_shown[$course->id] = $course->fullname;
        }
        return $courses_shown;
    }

    /**
     * Returns a selection of section of the selected course
     * @param int $course_id id of the selected course when loading (not dynamically)
     * @param int $section_id if of section if already selected when loading (optional, default=0)
     * @return array list of course sections which user can choose from
     */
    private function select_section(int $course_id, int $section_id=0){
        global $DB;
        $sections = null;
        if($course_id){
            if($section_id){
                $sections = $DB->get_records(
                    'course_sections',
                    array(
                        'course' => $course_id,
                        'section' => $section_id
                    )
                );
            } else {
                $sections = $DB->get_records(
                    'course_sections',
                    array(
                        'course' => $course_id
                    )
                );
            }
        } else {
            $sections = $DB->get_records('course_sections');
        }
        $sections_shown = array();
        $format = course_get_format($course_id);
        foreach ($sections as $sec) {
            $sections_shown[$sec->section] = $format->get_section_name($sec->section);
        }
        return $sections_shown;
    }
}
