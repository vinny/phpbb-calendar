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
    'ACP_EVENTBOARD' => 'EventBoard',
    'ACP_EVENTBOARD_SETTINGS' => 'Basic Settings',
    'ACP_EVENTBOARD_SETTINGS_EXPLAIN' => 'Configure the general options for the EventBoard calendar system.',
    'ACP_EVENTBOARD_MAP_SETTINGS' => 'Map Settings',
    'ACP_MAP_WIDTH' => 'Image Width (px)',
    'ACP_MAP_WIDTH_EXPLAIN' => 'Set the width of the static map image generated for events.',
    'ACP_MAP_HEIGHT' => 'Image Height (px)',
    'ACP_MAP_HEIGHT_EXPLAIN' => 'Set the height of the static map image generated for events.',
    'ACP_MAP_ZOOM' => 'Zoom Level',
    'ACP_MAP_ZOOM_EXPLAIN' => 'Default zoom level for the map view (1-20).',
    'ACP_MAP_LANG' => 'Map Language',
    'ACP_MAP_LANG_EXPLAIN' => 'Select the language for map labels.',
    'ACP_EVENTBOARD_CATEGORIES' => 'Categories',
    'ACP_EVENTBOARD_CATEGORIES_EXPLAIN' => 'Create and manage event categories.',
    'ACP_EVENTBOARD_MANAGE' => 'Manage Events',
    'ACP_EVENTBOARD_MANAGE_EXPLAIN' => 'View, edit, and delete events created by users.',
    'EVENTBOARD_ENABLE' => 'Enable Calendar',
    'EVENTBOARD_ENABLE_EXPLAIN' => 'Turn the calendar system on or off for the entire board.',
    'EVENTBOARD_ALLOW_COMMENTS' => 'Allow Comments',
    'EVENTBOARD_ALLOW_COMMENTS_EXPLAIN' => 'Allow users to post comments on event pages.',
    'EVENTBOARD_ENABLE_FEED' => 'Enable Feeds (RSS/Atom)',
    'EVENTBOARD_ENABLE_FEED_EXPLAIN' => 'Note: The phpBB Atom feed setting must be enabled in the ACP.',
    'EVENTBOARD_REMINDER_MINUTES' => 'Reminder period',
    'EVENTBOARD_REMINDER_MINUTES_EXPLAIN' => 'How many minutes before the event starts users should receive a reminder. Use 0 to disable notifications.',
    'EVENTBOARD_GEOAPIFY_KEY' => 'Geoapify API Key',
    'EVENTBOARD_GEOAPIFY_KEY_EXPLAIN' => 'Key for address autocomplete and maps. Get your Geoapify key <a href="https://myprojects.geoapify.com/" target="_blank">here</a>.<br />Important: In the Geoapify Dashboard, select the project > expand the dropdown arrow > under Allowed Origins click the add button > enter your site domain (e.g. https://www.mysite.com) > click Ok.',
    'EVENTBOARD_DISABLED' => 'The event calendar is disabled.',

    // ACP Logs & Messages
    'CATEGORY_ADDED' => 'Category added successfully.',
    'CATEGORY_UPDATED' => 'Category updated successfully.',
    'CATEGORY_DELETED' => 'Category deleted successfully.',
    'CONFIRM_DELETE_CATEGORY' => 'Are you sure you want to delete this category?',

    'CAT_NAME' => 'Category Name',
    'CAT_DESC' => 'Description',
    'CAT_COLOR' => 'Color (Hex)',
    'CAT_ICON' => 'Icon (FontAwesome)',
    'CREATE_CATEGORY' => 'Create New Category',
    'EDIT_CATEGORY' => 'Edit Category',
    'NO_ENTRIES' => 'No entries found',
    'ACTION' => 'Action',
    'NO_DESCRIPTION' => 'No description',
    'COLOUR_SWATCH' => 'Color Swatch',
    'ACP_EVENT_TOTAL' => 'Total events',
    'ACP_EVENT_VISIBILITY' => 'Visibility',

    // Flatpickr Settings
    'ACP_FP_SETTINGS' => 'Date/Time picker',
    'ACP_FP_THEME' => 'Calendar Theme',
    'ACP_FP_THEME_EXPLAIN' => 'Choose the color scheme for the date picker.',
    'ACP_FP_DATE_FORMAT' => 'Date and Time Format',
    'ACP_FP_DATE_FORMAT_EXPLAIN' => 'Use compatible formats (e.g. d/m/Y H:i).',
    'ACP_FP_TIME_24HR' => 'Use 24h Time',
    'ACP_FP_TIME_24HR_EXPLAIN' => 'If disabled, AM/PM will be used.',
    'ACP_FP_LANGUAGE' => 'Calendar Language',
    'ACP_FP_LANGUAGE_EXPLAIN' => 'Select the language for the date picker interface.',


    // Config Options
    'DEFAULT' => 'Default',
    'DEFAULT_LIGHT' => 'Default (Light)',
    'ENGLISH_DEFAULT' => 'English (Default)',
    
    // Logs
    'LOG_EVENTBOARD_CONFIG_UPDATED' => '<strong>Updated EventBoard settings</strong>',
    'LOG_EVENTBOARD_CATEGORY_ADDED' => '<strong>Added EventBoard category</strong><br />» %s',
    'LOG_EVENTBOARD_CATEGORY_UPDATED' => '<strong>Updated EventBoard category</strong><br />» %s',
    'LOG_EVENTBOARD_CATEGORY_REMOVED' => '<strong>Deleted EventBoard category</strong><br />» %s',
));
