<?php
// This file is part of the development project WS 21/22 by the university of Stuttgart

/**
 * Navigation settings and helper functions
 *
 * @package    booktool_genbook
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Callback function
 * Adds module specific settings to the course settings block
 *
 * @param navigation_node $node The node to add module settings to
 * @param stdClass $course The course the settings block belongs to
 * @param context_course $context Context of the course
 */
function booktool_genbook_extend_navigation_course(navigation_node $node, stdClass $course, context_course $context) {
    global $PAGE;

    if (has_capability('booktool/genbook:generate', $context)) {
        $course_id = $course->id;
        $url = new moodle_url('/mod/book/tool/genbook/index.php', array('cid' => $course_id));
        $icon = new pix_icon('i/settings', '', 'moodle', array('class' => 'iconsmall'));
        $node->add(get_string('settings_name', 'booktool_genbook'), $url, navigation_node::TYPE_COURSE, null, null, $icon);
    }
}

/**
 * Callback function
 * Adds module specific settings to the book settings block
 *
 * @param settings_navigation $settings
 * @param navigation_node $node The node to add module settings to
 */
function booktool_genbook_extend_settings_navigation(settings_navigation $settings, navigation_node $node) {
    global $PAGE;

    if (has_capability('booktool/genbook:generate', $PAGE->cm->context)) {
        $course_id = $PAGE->cm->course->id;
        $url = new moodle_url('/mod/book/tool/genbook/index.php', array('cid' => $course_id));
        $icon = new pix_icon('i/settings', '', 'moodle', array('class' => 'iconsmall'));
        $node->add(get_string('settings_name', 'booktool_genbook'), $url, navigation_node::TYPE_SETTING, null, null, $icon);
    }
}

/**
 * Returns the content of the file found with the itemid
 * 
 * @param int $itemid itemid of the config file
 * @return string content of file
 */
function get_config_file(int $itemid) {
    // Get information about file
    global $DB;
    $config_info = $DB->get_record(
        'files',
        array(
            'itemid' => $itemid,
            'mimetype' => 'text/plain'
        ),
        '*',
        MUST_EXIST
    );

    // Get config file object
    $fs = get_file_storage();
    $file = $fs->get_file(
        $config_info->contextid,
        $config_info->component,
        'draft',
        $itemid,
        $config_info->filepath,
        $config_info->filename
    );
    $name = str_replace('.config', '', $config_info->filename);
    $contents = $file->get_content();
    return $contents;
}

/**
 * Parses the content of a config file of format:
 *      [filename_chaptername(some sort of linebreak)]
 * @param string $content of the config file
 * @return array('filename'=> 'chaptername', ...)
 */
function parse_config(string $content) {
    $content = preg_replace('/([^\s]+)_/', '@$0', $content);
    $lines = explode('@', $content);
    array_shift($lines);
    $res = array();
    foreach ($lines as $line) {
        $line = explode('_', $line);
        $res[$line[0]] = $line[1];
    }
    return $res;
}

function get_chapter_file(int $itemid, string $filename) {
    global $DB;
    $select = $DB->sql_like('filename', ':filename', false) . "AND itemid = :itemid";
    $file = $DB->get_record_select('files', $select, array('itemid' => $itemid, 'filename' => "$filename.%"));
    $fs = get_file_storage();
    $file = new stored_file($fs, $file);
    return $file;
}
