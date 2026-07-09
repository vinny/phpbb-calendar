<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license All Rights Reserved
 *
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'ACL_CAT_EVENTBOARD'        => 'Events',
    'ACL_U_EVENTBOARD_VIEW'     => 'Can view the events page',
    'ACL_U_EVENTBOARD_CREATE'   => 'Can add events',
    'ACL_U_EVENTBOARD_DELETE'   => 'Can delete own events',
    'ACL_U_EVENTBOARD_COMMENT'  => 'Can use the comments system (read, post, delete own comments)',
));
