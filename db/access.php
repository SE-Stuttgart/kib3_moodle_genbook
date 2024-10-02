<?php
// This file is part of the development project WS 21/22 by the university of Stuttgart

/**
 * Book module capability definition
 *
 * @package    booktool_genbook
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$capabilities = array(
    'booktool/genbook:generate' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        ),
    ),
);
