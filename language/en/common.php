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
	'EVENT_CALENDAR' => 'Calendar',
	'EVENT_CALENDAR_EXPLAIN' => 'View events and calendar',
	'UNCATEGORIZED' => 'Uncategorized',
	'EVENT_CREATE' => 'Create event',
	'EVENT_TITLE' => 'Title',
	'EVENT_DESCRIPTION' => 'Description',
	'EVENT_START' => 'Start date',
	'EVENT_END' => 'End date',
	'EVENT_DATE_RANGE' => 'Event dates',
	'EVENT_LOCATION' => 'Location',
	'EVENT_LEAVE' => 'Leave event',
	'NO_EVENTS' => 'No events found.',
	'COMING_UP' => 'Coming up',
	'EVENTBOARD_DISABLED' => 'The event calendar is disabled.',
	'NO_ENTRIES' => 'No entries found.',

	// View Online Strings
	'VIEWING_EVENT_CALENDAR' => 'Viewing event calendar',
	'VIEWING_EVENT' => 'Viewing event: %s',
	'CREATING_EVENT' => 'Creating an event',
	'EDITING_EVENT' => 'Editing an event',
	'VIEWING_UPCOMING_EVENTS' => 'Viewing upcoming events',
	'VIEWING_EVENT_CATEGORY' => 'Viewing event category: %s',
	'VIEWING_MY_EVENTS' => 'Viewing my events',
	'VIEWING_MY_RSVPS' => 'Viewing my RSVPs',

	'EVENT_TITLE_REQUIRED' => 'The event title is required.',
	'EVENT_TITLE_TOO_LONG' => 'The event title cannot exceed 255 characters.',
	'EVENT_CATEGORY_REQUIRED' => 'You must select a category.',
	'EVENT_DESCRIPTION_REQUIRED' => 'The event description is required.',
	'EVENT_LOCATION_REQUIRED' => 'The location field is required for in-person events.',
	'EVENT_LOCATION_TOO_LONG' => 'The event location cannot exceed 255 characters.',
	'EVENT_START_INVALID' => 'The start date is invalid.',
	'EVENT_END_INVALID' => 'The end date must be after the start date.',
	'EVENT_LIMIT_INVALID' => 'The participants limit must be greater than zero.',
	'EVENT_ATTENDEES_LIMIT_EXPLAIN' => 'Maximum number of participants, including the organizer.',

	// Create Event Form
	'EVENT_DETAILS' => 'Event details',
	'EVENT_CATEGORY' => 'Category',
	'SELECT_CATEGORY' => 'Select a category',
	'EVENT_FORMAT' => 'Event format',
	'FORMAT_IN_PERSON' => 'In-person',
	'FORMAT_ONLINE' => 'Online',
	'EVENT_GEO_SEARCH_PLACEHOLDER' => 'Search for address or city...',
	'LIMIT_REGISTRATIONS' => 'Limit registrations',
	'PUBLIC_CALENDAR' => 'Public calendar',
	'PUBLIC_CALENDAR_EXPLAIN' => 'Display on the community main calendar',
	'PUBLIC_CALENDAR_PRIVATE_EXPLAIN' => 'Only people with the link can view event details and join.',
	'MAP_PREVIEW' => 'Map preview',
	'COPIED' => 'Copied!',

	'EVENT_DELETED' => 'Event deleted.',
	'EVENT_NOT_FOUND' => 'Event not found.',
	'EVENT_ENDED' => 'This event has ended.',
	'EVENT_OCCURRING' => 'This event is occurring now.',
	'POST_COMMENT' => 'Post a comment',
	'EVENT_SUBMIT' => 'Submit',
	'MESSAGE_EMPTY' => 'You cannot submit an empty comment.',
	'DELETE_COMMENT' => 'Delete comment',
	'COMMENT_NOT_FOUND' => 'Comment not found.',
	'EVENT_FULL' => 'This event has reached its maximum participant limit.',
	'EVENT_ALREADY_JOINED' => 'You are already registered for this event.',
	'EVENT_COMMENTS_CLOSED' => 'Comments are closed for ended events.',
	'SHARE_EVENT' => 'Share this event',
	'EVENT_ACCESS_DENIED' => 'You do not have access to this event.',
	'EVENT_OWNER_CANNOT_RSVP' => 'The event organizer cannot RSVP to their own event.',
	'EVENT_OWNER_CANNOT_LEAVE' => 'The event organizer cannot leave their own event.',

	// Event View Strings
	'EVENT_ORGANIZED_BY' => 'Organized by',
	'HOSTED_BY' => 'Hosted by %s',
	'EVENT_ENDS' => 'Ends',
	'WHO_IS_GOING' => "Who’s going",
	'ADD_TO_CALENDAR' => 'Add to calendar',
	'GOOGLE_CALENDAR' => 'Google Calendar',
	'OUTLOOK_CALENDAR' => 'Outlook Calendar',
	'YAHOO_CALENDAR' => 'Yahoo Calendar',
	'COPY_LINK' => 'Copy link',
	'SHARE_WHATSAPP' => 'WhatsApp',
	'SHARE_FACEBOOK' => 'Facebook',
	'SHARE_TWITTER' => 'X / Twitter',
	'SHARE_TELEGRAM' => 'Telegram',
	'RSS_FEED' => 'RSS Feed',
	'EXPORT_ICS' => 'Export .ics',
	'EVENT_GEO_PROXY_FETCH_FAILED' => 'The location service could not be reached.',
	'EVENT_GEO_PROXY_INVALID_JSON' => 'The location service returned an invalid response.',
	'EVENT_GEO_PROXY_RATE_LIMITED' => 'Too many requests. Please slow down.',
	'CONFIRM_ATTENDANCE' => 'Confirm attendance',
	'MY_RSVPS' => 'My RSVPs',
	'NO_RSVPS_FOUND' => 'No one has confirmed attendance yet.',
	'CAPACITY' => 'Capacity',
	'EVENT_ONLINE' => 'Online',

	'CATEGORY_NOT_FOUND' => 'Category not found.',

	// Upcoming Events Page
	'ATTENDING' => 'Attending',
	'NO_UPCOMING_EVENTS' => 'No upcoming events found.',
	'EVENTS_COUNT' => array(
		1 => '%d event',
		2 => '%d events',
	),
	'MY_EVENTS' => 'My Events',
	'ACTIVE_EVENTS' => 'Active Events',
	'EVENT_COMPLETED' => 'Completed',
	'SIGNUPS' => 'Sign-ups',
	'EVENT_ACTIONS' => 'Actions',
	'NO_EVENTS_FOUND' => 'No events found',
	'UNLIMITED_SPOTS' => 'Unlimited',
	'HAPPENING_NOW' => 'Events Happening Now',
	'TOTAL_EVENTS' => 'Total events <strong>%d</strong>',

	// Category View Page
	'UPCOMING_EVENTS_LIST' => 'Upcoming events',
	'CONFIRM_DELETE_EVENT' => 'Are you sure you want to delete this event? This will also remove all participants and comments.',
	'CONFIRM_DELETE_COMMENT' => 'Are you sure you want to delete this comment?',
	'RETURN_TO_MY_EVENTS' => 'Return to my events',
	'EDIT_EVENT' => 'Edit event',
	'EVENT_STATUS' => 'Status',

	// Notifications
	'VINNY_CALENDAR_NOTIFICATION_PARTICIPANT_ADDED' => 'Someone joined your event',
	'VINNY_CALENDAR_NOTIFICATION_PARTICIPANT_ADDED_TITLE' => '<strong>%1$s</strong> has joined your event: <strong>%2$s</strong>',
	'VINNY_CALENDAR_NOTIFICATION_NEW_COMMENT' => 'Someone commented on an event you organize or are participating in',
	'VINNY_CALENDAR_NOTIFICATION_NEW_COMMENT_TITLE' => '<strong>%1$s</strong> commented on the event you organize or are participating in: <strong>%2$s</strong>',
	'VINNY_CALENDAR_NOTIFICATION_EVENT_REMINDER' => 'An event you organize or are participating in is starting',
	'VINNY_CALENDAR_NOTIFICATION_EVENT_REMINDER_TITLE' => 'An event you organize or are participating in, <strong>%1$s</strong>, starts in <strong>%2$d minutes</strong>',
	'NOTIFICATION_GROUP_EVENTS' => 'EventBoard Notifications',

	// Flatpickr inline language translations
	'FLATPICKR_SUN' => 'Sun',
	'FLATPICKR_MON' => 'Mon',
	'FLATPICKR_TUE' => 'Tue',
	'FLATPICKR_WED' => 'Wed',
	'FLATPICKR_THU' => 'Thu',
	'FLATPICKR_FRI' => 'Fri',
	'FLATPICKR_SAT' => 'Sat',
	'FLATPICKR_SUNDAY' => 'Sunday',
	'FLATPICKR_MONDAY' => 'Monday',
	'FLATPICKR_TUESDAY' => 'Tuesday',
	'FLATPICKR_WEDNESDAY' => 'Wednesday',
	'FLATPICKR_THURSDAY' => 'Thursday',
	'FLATPICKR_FRIDAY' => 'Friday',
	'FLATPICKR_SATURDAY' => 'Saturday',
	'FLATPICKR_JAN' => 'Jan',
	'FLATPICKR_FEB' => 'Feb',
	'FLATPICKR_MAR' => 'Mar',
	'FLATPICKR_APR' => 'Apr',
	'FLATPICKR_MAY' => 'May',
	'FLATPICKR_JUN' => 'Jun',
	'FLATPICKR_JUL' => 'Jul',
	'FLATPICKR_AUG' => 'Aug',
	'FLATPICKR_SEP' => 'Sep',
	'FLATPICKR_OCT' => 'Oct',
	'FLATPICKR_NOV' => 'Nov',
	'FLATPICKR_DEC' => 'Dec',
	'FLATPICKR_JANUARY' => 'January',
	'FLATPICKR_FEBRUARY' => 'February',
	'FLATPICKR_MARCH' => 'March',
	'FLATPICKR_APRIL' => 'April',
	'FLATPICKR_MAY_LONG' => 'May',
	'FLATPICKR_JUNE' => 'June',
	'FLATPICKR_JULY' => 'July',
	'FLATPICKR_AUGUST' => 'August',
	'FLATPICKR_SEPTEMBER' => 'September',
	'FLATPICKR_OCTOBER' => 'October',
	'FLATPICKR_NOVEMBER' => 'November',
	'FLATPICKR_DECEMBER' => 'December',
	'FLATPICKR_RANGE_SEPARATOR' => ' to ',
	'FLATPICKR_WEEK_ABBREVIATION' => 'Wk',
	'FLATPICKR_SCROLL_TITLE' => 'Scroll to increment',
	'FLATPICKR_TOGGLE_TITLE' => 'Click to toggle',
	'FLATPICKR_AM' => 'AM',
	'FLATPICKR_PM' => 'PM',
	'FLATPICKR_YEAR_ARIA_LABEL' => 'Year',
	'FLATPICKR_HOUR_ARIA_LABEL' => 'Hour',
	'FLATPICKR_MINUTE_ARIA_LABEL' => 'Minute',

	// Set the 2-letter language code for static map images (e.g. 'en', 'pt', 'es', 'de', 'fr').
	// Full list of ISO 639-1 language codes: https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes
	'CALENDAR_MAP_LANG'			=> 'en',
));
