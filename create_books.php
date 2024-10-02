<?php
// This file is part of the development project WS 21/22 by the university of Stuttgart

/**
 * Lib functions for book generation.
 *
 * @package    booktool_genbook
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/book/mod_form.php');
require_once($CFG->dirroot . '/mod/book/edit_form.php');

/**
 * @param int $course_id | id of the course the book is to be created in
 * @param int $section_id | id of the section the book is to be created in
 * @param string $name | name of the book
 * @param string $description_content (default='')
 * @param bool $show_description | show the description in course overview? | default=false
 * @return object module_info of the new book module
 */
function create_book($course_id, $section_id, $name, string $description_content = '', bool $show_description = false)
{
    global $DB;
    $add = 'book';

    $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

    // MDL-69431 Validate that $section (url param) does not exceed the maximum for this course / format.
    // If too high (e.g. section *id* not number) non-sequential sections inserted in course_sections table.
    // Then on import, backup fills 'gap' with empty sections (see restore_rebuild_course_cache). Avoid this.
    $courseformat = course_get_format($course);
    $maxsections = $courseformat->get_max_sections();
    if ($section_id > $maxsections) {
        print_error('maxsectionslimit', 'moodle', '', $maxsections);
    }

    list($module, $context, $cw, $cm, $data) = prepare_new_moduleinfo_data($course, $add, $section_id);
    $data->return = 0;
    $data->sr = null;
    $data->add = $add;

    $data->name = $name;
    $data->introeditor['text'] = $description_content;
    $data->introeditor['format'] = FORMAT_HTML;
    $data->showdescription = $show_description;

    if (!empty($type)) { //TODO: hopefully will be removed in 2.0
        $data->type = $type;
    }

    $mform = new mod_book_mod_form($data, $cw->section, $cm, $course);
    $mform->set_data($data);
    $module_info = add_moduleinfo($data, $course, $mform);
    return $module_info;
}

/**
 * @param int $cmid | course_module_id of the book this chapter is to be created in
 * @param int $pagenum | the number of the chapter
 * @param string $title | title of the chapter
 * @param stored_file $file | .png file
 * @return object DB record of the new chapter
 */
function create_chapter($cmid, $pagenum, $title, stored_file $file)
{
    global $DB;

    $cm = get_coursemodule_from_id('book', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $book = $DB->get_record('book', array('id' => $cm->instance), '*', MUST_EXIST);

    $context = context_module::instance($cm->id);

    // Create empty new chapter

    $data = new stdClass();
    $data->id         = null;
    $data->subchapter = 0;
    $data->pagenum    = $pagenum + 1;
    $data->cmid = $cm->id;
    $data->title = $title;
    $data->bookid        = $book->id;
    $data->hidden        = 0;
    $data->timecreated   = time();
    $data->timemodified  = time();
    $data->importsrc     = '';
    $data->content       = '';          // updated later
    $data->contentformat = FORMAT_HTML; // updated later 
    if ($prevpage = $data->pagenum - 1) {
        $currentchapter = $DB->get_record('book_chapters', ['pagenum' => $prevpage, 'bookid' => $book->id]);
        if ($currentchapter) {
            $data->currentchaptertitle = $currentchapter->title;
        }
    }

    // make room for new page
    $sql = "UPDATE {book_chapters}
               SET pagenum = pagenum + 1
             WHERE bookid = ? AND pagenum >= ?";
    $DB->execute($sql, array($book->id, $data->pagenum));

    $data->id = $DB->insert_record('book_chapters', $data);


    // update chapter with chapter content
    $fs = get_file_storage();

    $filerecord = array(
        'contextid' => $context->id,        // ID of context
        'component' => 'mod_book',          // usually = table name
        'filearea' => 'chapter',            // usually = table name
        'itemid' => $data->id,              // usually = ID of row in table
        'filepath' => '/',                  // any path beginning and ending in /
        'filename' => $file->get_filename() //$content->get_filename() // any filename
    );

    $new_file = $fs->create_file_from_storedfile($filerecord, $file);

    $content = '<p dir="ltr" style="text-align: left;"><img src="';
    $content .= '@@PLUGINFILE@@' . $new_file->get_filepath() . $new_file->get_filename();
    $content .= '" alt="" role="presentation" class="img-fluid atto_image_button_text-bottom" width="1600" height="900"><br></p>';

    $data->content_editor['text'] = $content;
    $data->content_editor['format'] = FORMAT_HTML;

    $options = array('noclean' => true, 'subdirs' => true, 'maxfiles' => -1, 'maxbytes' => 0, 'context' => $context);

    $data = file_postupdate_standard_editor($data, 'content', $options, $context, 'mod_book', 'chapter', $data->id);

    $DB->update_record('book_chapters', $data);
    $DB->set_field('book', 'revision', $book->revision + 1, array('id' => $book->id));
    $chapter = $DB->get_record('book_chapters', array('id' => $data->id));

    return $chapter;
}
