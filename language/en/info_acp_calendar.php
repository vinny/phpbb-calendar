<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
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
	'EVENTBOARD_DISPLAY_OCCURRING' => 'Display "Events Happening Now" block on index',
	'EVENTBOARD_DISPLAY_OCCURRING_EXPLAIN' => 'Show a block on the forum index page displaying events that are currently taking place.',
	'EVENTBOARD_DISPLAY_UPCOMING' => 'Display "Upcoming Events" block on index',
	'EVENTBOARD_DISPLAY_UPCOMING_EXPLAIN' => 'Show a block on the forum index page with the list of the next 5 upcoming events.',
	'EVENTBOARD_DISPLAY_STATS' => 'Display total events in statistics',
	'EVENTBOARD_DISPLAY_STATS_EXPLAIN' => 'Show the total number of public events in the forum statistics on the index page.',

	// ACP Logs & Messages
	'CATEGORY_ADDED' => 'Category added successfully.',
	'CATEGORY_UPDATED' => 'Category updated successfully.',
	'CATEGORY_DELETED' => 'Category deleted successfully.',
	'CONFIRM_DELETE_CATEGORY' => 'Are you sure you want to delete this category?',

	'CAT_NAME' => 'Category Name',
	'CAT_DESC' => 'Description',
	'CAT_COLOR' => 'Color',
	'CAT_ICON' => 'Icon (FontAwesome)',
	'CREATE_CATEGORY' => 'Create New Category',
	'EDIT_CATEGORY' => 'Edit Category',
	'NO_ENTRIES' => 'No entries found',
	'ACTION' => 'Action',
	'NO_DESCRIPTION' => 'No description',
	'COLOUR_SWATCH' => 'Color Swatch',
	'ACP_EVENT_TOTAL' => 'Total events',
	'ACP_EVENT_VISIBILITY' => 'Visibility',
	'CATEGORY_NAME_EXPLAIN' => 'Name of the event category (e.g., Meetups, Gaming).',
	'CATEGORY_COLOR_EXPLAIN' => 'Color used to display the category in the calendar.',
	'CATEGORY_ICON_EXPLAIN' => 'FontAwesome icon class name (e.g. fa-users, fa-gamepad). Icons can be viewed on <a href="https://fontawesome.com/v4/icons/" target="_blank" rel="noopener">FontAwesome</a>.',
	'CAT_EVENT_COUNT' => 'Event Count',
	'CATEGORY_HAS_EVENTS' => 'This category cannot be deleted because it has events assigned to it',
	'CATEGORY_NAME_REQUIRED' => 'You must specify a category name.',
	'CATEGORY_COLOR_INVALID' => 'You must select a valid color.',
	'CATEGORY_ICON_INVALID' => 'You must enter a valid FontAwesome icon class (e.g. fa-users).',
	'PUBLIC' => 'Public',
	'PRIVATE' => 'Private',

	// Logs
	'LOG_EVENTBOARD_CONFIG_UPDATED' => '<strong>Updated EventBoard settings</strong>',
	'LOG_EVENTBOARD_CATEGORY_ADDED' => '<strong>Added EventBoard category</strong><br />» %s',
	'LOG_EVENTBOARD_CATEGORY_UPDATED' => '<strong>Updated EventBoard category</strong><br />» %s',
	'LOG_EVENTBOARD_CATEGORY_REMOVED' => '<strong>Deleted EventBoard category</strong><br />» %s',
));
