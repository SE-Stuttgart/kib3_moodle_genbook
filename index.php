<?php
// This file is part of the development project WS 21/22 by the university of Stuttgart

/**
 * Page for generating book out of pictures
 *
 * @package    booktool_genbook
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../../config.php');
require_once($CFG->dirroot . '/mod/book/tool/genbook/genbook_form.php');
require_once($CFG->dirroot . '/mod/book/tool/genbook/create_books.php');
require_once($CFG->dirroot . '/mod/book/tool/genbook/lib.php');

$context = context_system::instance();
require_login();

// set page url
$course_id = required_param('cid', PARAM_INT);
$section_id = optional_param('sid', 0, PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL); // A return URL. returnto must also be set to 'url'.
if($returnurl){
    $returnurl = new moodle_url($returnurl);
} else {
    if (!empty($course_id)) {
        $returnurl = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $course_id));
    } else {
        $returnurl = new moodle_url($CFG->wwwroot . '/course/');
    }
}
$PAGE->set_url(new moodle_url('/mod/book/tool/genbook/index.php'), array('returnurl'=>$returnurl));

// Build page
$PAGE->set_context($context);
$PAGE->set_title(get_string('title_genbook', 'booktool_genbook'));
$PAGE->set_heading(get_string('heading_genbook', 'booktool_genbook'));

// form initialization
$args = array(
    'cid'=>$course_id,
    'sid'=>$section_id,
    'returnurl'=>$returnurl
);
$mform = new generate_book_form(null, $args);
if ($mform->is_cancelled()) {
    redirect($returnurl);
    //course_get_url($course_id, $section_id)
} else if ($data = $mform->get_data()) {
    // TODO Validate input files
    $config_content = get_config_file($data->configuration);
    $separated_conf = parse_config($config_content);
    // create book
    $book = create_book($data->cid, $data->sid,$data->bookname);
    // create chapters
    $pagenum = 1;
    foreach($separated_conf as $file=>$title){
        $content = get_chapter_file($data->attachments, $file);
        create_chapter(
            $book->coursemodule,
            $pagenum,
            $title,
            $content
        );
        $pagenum++;
    }
    // set returnurl to new book
    $returnurl = new moodle_url($CFG->wwwroot . '/mod/book/view.php', array('id' => $book->coursemodule));
    redirect($returnurl);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
