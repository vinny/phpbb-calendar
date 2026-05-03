<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GPL-2.0-only
 *
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'EVENT_CALENDAR' => 'Events',
    'EVENT_VIEW' => 'View Event',
    'EVENT_CREATE' => 'Create Event',
    'EVENT_TITLE' => 'Title',
    'EVENT_DESCRIPTION' => 'Description',
    'EVENT_START' => 'Start Date',
    'EVENT_END' => 'End Date',
    'EVENT_LOCATION' => 'Location',
    'EVENT_JOIN' => 'Join',
    'EVENT_LEAVE' => 'Leave Event',
    'EVENT_PARTICIPANTS' => 'Participants',
    'NO_EVENTS' => 'No events found.',
    'CALENDAR_VIEW' => 'Calendar View',
    'LIST_VIEW' => 'List View',

    'ALL_EVENTS' => 'All Categories',
    'MEETINGS' => 'Meetups',
    'GAMING' => 'Gaming',
    'SOCIAL' => 'Social',
    'COMING_UP' => 'Coming Up',
    'TODAY' => 'Today',


    'EVENT_CREATED' => 'Event created successfully.',
    'EVENT_LOCATION_REQUIRED' => 'The location field is required for in-person events.',
    'RETURN_TO_CALENDAR' => 'Click %shere%s to return to the calendar.',

    // Create Event Form
    'EVENT_CREATE_DESC' => 'Fill in the details below to schedule a new community meetup.',
    'EVENT_DETAILS' => 'Event Details',
    'EVENT_TITLE_PLACEHOLDER' => 'Ex: Annual Coding BBQ',
    'CATEGORY' => 'Category',
    'SELECT_CATEGORY' => 'Select a category',
    'EVENT_FORMAT' => 'Event Format',
    'FORMAT_IN_PERSON' => 'In-Person',
    'FORMAT_ONLINE' => 'Online',
    'EVENT_LOCATION_PLACEHOLDER' => 'Search for a location',
    'EVENT_GEO_SEARCH_PLACEHOLDER' => 'Search for address or city...',
    'EVENT_DESC_PLACEHOLDER' => 'Describe the event agenda, guest speakers, and what attendees should bring...',
    'PREVIEW' => 'Preview',
    'SUBMIT_EVENT' => 'Submit Event',
    'EVENT_SETTINGS' => 'Event Settings',
    'LIMIT_REGISTRATIONS' => 'Limit Registrations',
    'LIMIT_REGISTRATIONS_EXPLAIN' => 'Cap the maximum number of attendees',
    'MAX_ATTENDEES' => 'Max. Attendees',
    'ALLOW_DISCUSSION' => 'Allow Discussion',
    'ALLOW_DISCUSSION_EXPLAIN' => 'Enable comments on the event page',
    'PUBLIC_CALENDAR' => 'Public Calendar',
    'PUBLIC_CALENDAR_EXPLAIN' => 'Display on the community main calendar',
    'PUBLIC_CALENDAR_PRIVATE_EXPLAIN' => 'Only people with the link can view event details and join.',
    'PRO_TIP' => 'Pro Tip',
    'PRO_TIP_TEXT' => 'Adding an image to your description increases engagement by 40%.',
    'MAP_PREVIEW' => 'Map Preview',



    // Missing Keys added in MVP Phase
    'EVENT_DELETED' => 'Event deleted successfully.',
    'EVENT_NOT_FOUND' => 'The requested event could not be found.',
    'EVENT_ENDED' => 'This event has ended.',
    'ACP_EVENTBOARD_MANAGE_EVENTS' => 'Manage Events',
    'COMMENTS' => 'Comments',
    'POST_COMMENT' => 'Post a Comment',
    'NO_COMMENTS' => 'No comments yet. Be the first to start the discussion!',
    'SUBMIT' => 'Submit',
    'MESSAGE_EMPTY' => 'You cannot submit an empty comment.',
    'DELETE_COMMENT' => 'Delete comment',
    'COMMENT_DELETED' => 'Comment deleted successfully.',
    'COMMENT_NOT_FOUND' => 'The requested comment could not be found.',
    'ALREADY_JOINED' => 'You have already joined this event.',
    'EVENT_FULL' => 'This event has reached its maximum participant limit.',
    'NO_PARTICIPANTS' => 'No participants yet.',
    'SHARE_EVENT' => 'Share this Event',
    'COMMENTS_DISABLED' => 'Comments are currently disabled for this board.',
    'EVENT_ACCESS_DENIED' => 'You do not have access to this event.',
    'EVENT_OWNER_CANNOT_RSVP' => 'The event organizer cannot RSVP to their own event.',
    
    // Posting Options
    'DISABLE_BBCODE' => 'Disable BBCode',
    'DISABLE_SMILIES' => 'Disable Smilies',
    'DISABLE_MAGIC_URL' => 'Do not automatically parse URLs',
    'MORE_SMILIES' => 'View more smilies',
    
    // Event View Strings
    'EVENT_ORGANIZER' => 'Event Organizer',
    'CONTACT_ORGANIZER' => 'Contact Organizer',
    'WHO_IS_GOING' => "Who's Going",
    'SEE_ALL' => 'See all',
    'ADD_TO_CALENDAR' => 'Add to Calendar',
    'REGISTRATION' => 'Registration',
    'RSVP_REQUIRED' => 'RSVP is required for food planning.',
    'CONFIRM_ATTENDANCE' => 'Confirm Attendance',
    'MY_RSVPS' => 'My RSVPs',
    'NO_RSVPS_FOUND' => 'You have not RSVP\'d to any upcoming events.',
    'LEAVE' => 'Leave',
    'VIEW' => 'View',
    'VIEW_EVENT' => 'View Event',
    'SEATS_FILLING_FAST' => 'Seats filling fast!',
    'REGISTRATION_CLOSES' => 'Registration closes in %s days',
    'CAPACITY' => 'Capacity',
    'OPEN_FOR_REGISTRATION' => 'Open for Registration',
    'JOIN_CONVERSATION' => 'Comments & Discussion',
    'JOIN_CONVERSATION_EXPLAIN' => 'Ask questions or coordinate carpooling in the forum thread.',
    'GO_TO_TOPIC' => 'Go to Topic',
    'MAP_PREVIEW' => 'Map Preview',
    'BOARD_INDEX' => 'Board index',
    'EVENT_ONLINE' => 'Online',
    'EVENT_PUBLIC' => 'Public Event',
    'LOGIN_EXPLAIN_EVENT' => 'Please login to continue.',

    'CATEGORY_NOT_FOUND' => 'The requested category could not be found.',
    'CATEGORY_NAME_REQUIRED' => 'The category name is required.',
    'CATEGORY_COLOR_INVALID' => 'The category color must be a valid hex code.',
    'CATEGORY_ICON_INVALID' => 'The category icon is invalid.',
    'CATEGORY_HAS_EVENTS' => 'This category cannot be deleted because it has events assigned to it.',

    // Upcoming Events Page
    'ATTENDING' => 'attending',
    'DETAILS' => 'Details',
    'NO_UPCOMING_EVENTS' => 'No upcoming events found.',
    'PAGINATION_SHOWING' => 'Showing',
    'PAGINATION_TO' => 'to',
    'PAGINATION_OF' => 'of',
    'EVENTS_LOWER' => 'events',
    'MY_EVENTS' => 'My Events',
    'MY_RSVPS' => 'My RSVPs',
    'ACTIVE_EVENTS' => 'Active Events',
    'TOTAL_SIGNUPS' => 'Total Sign-ups',
    'TOTAL_CREATED_EVENTS' => 'Total Created Events',
    'COMPLETED' => 'Completed',
    'SIGNUPS' => 'Sign-ups',
    'ACTIONS' => 'Actions',
    'NO_EVENTS_FOUND' => 'No events found',
    'NO_EVENTS_FOUND_DESC' => 'Get started by creating your first community event.',
    'UNLIMITED_SPOTS' => 'Unlimited',

    'CATEGORIES' => 'Categories',

    // Category View Page
    'ACTIVE_FILTER' => 'Active Filter',
    'HOST_EVENT_PROMO_TITLE' => 'Host an event?',
    'HOST_EVENT_PROMO_DESC' => 'Sharing interests with the community is easy and free.',
    'GET_STARTED' => 'Get Started',
    'UPCOMING_EVENTS' => 'Upcoming',
    'CLICK_VIEW_EVENT' => '%sClick here to view the event%s',
    'CONFIRM_DELETE_EVENT' => 'Are you sure you want to delete this event? This will also remove all participants and comments.',
    'RETURN_TO_MY_EVENTS' => 'Return to My Events',
    'RETURN_TO_CALENDAR_INDEX' => 'Return to Calendar Index',
    'EDIT_EVENT' => 'Edit Event',
    'SAVE_CHANGES' => 'Save Changes',
    'DELETE_MAP_IMAGE' => 'Delete Map Image',
    'EVENT_UPDATED' => 'Event updated successfully.',
    'MAP_IMAGE_DELETED' => 'Map image deleted successfully.',
    'CONFIRM_DELETE_MAP_IMAGE' => 'Are you sure you want to delete this map image?',
    'EDIT_MAP_EXPLAIN' => 'To update the map image, simply select a new location.',
    'NEWEST_EVENTS' => 'Newest',
    'LOGIN_TO_PARTICIPATE' => 'Please login to participate.',
    'LOGIN_TO_COMMENT' => 'Please login to join the conversation.',
    'STATUS' => 'Status',

    // Notifications
    'VINNY_CALENDAR_NOTIFICATION_PARTICIPANT_ADDED' => 'Someone joins your event',
    'VINNY_CALENDAR_NOTIFICATION_PARTICIPANT_ADDED_TITLE' => '<strong>%1$s</strong> has joined your event: <strong>%2$s</strong>',
    'VINNY_CALENDAR_NOTIFICATION_NEW_COMMENT' => 'Someone posts a comment on an event',
    'VINNY_CALENDAR_NOTIFICATION_NEW_COMMENT_TITLE' => '<strong>%1$s</strong> commented on the event: <strong>%2$s</strong>',
    'VINNY_CALENDAR_NOTIFICATION_EVENT_REMINDER' => 'An event reminder is sent',
    'VINNY_CALENDAR_NOTIFICATION_EVENT_REMINDER_TITLE' => 'Your event <strong>%1$s</strong> starts in <strong>%2$d minutes</strong>',
    'NOTIFICATION_GROUP_EVENTS' => 'EventBoard Notifications',
));
