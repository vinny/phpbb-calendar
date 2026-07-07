<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GPL-2.0-only
 *
 */

namespace vinny\calendar\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\pagination;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class main
{
	protected $config;
	protected $template;
	protected $user;
	protected $helper;
	protected $request;
	protected $auth;
	protected $pagination;
	protected $event_access;
	protected $event_query;
	protected $comment_service;
	protected $feed_service;
	protected $calendar_link;
	protected $event_display;
	protected $calendar_add;
	protected $share;
	protected $geo_proxy;
	protected $event_form;
	protected $event_manager;
	protected $rsvp;
	protected $root_path;
	protected $php_ext;

	public function __construct(
		config $config,
		template $template,
		user $user,
		helper $helper,
		request $request,
		auth $auth,
		pagination $pagination,
		\vinny\calendar\service\event_access $event_access,
		\vinny\calendar\service\event_query $event_query,
		\vinny\calendar\service\comment $comment_service,
		\vinny\calendar\service\feed $feed_service,
		\vinny\calendar\service\calendar_link $calendar_link,
		\vinny\calendar\service\event_display $event_display,
		\vinny\calendar\service\calendar_add $calendar_add,
		\vinny\calendar\service\share $share,
		\vinny\calendar\service\geo_proxy $geo_proxy,
		\vinny\calendar\service\event_form $event_form,
		\vinny\calendar\service\event_manager $event_manager,
		\vinny\calendar\service\rsvp $rsvp,
		$root_path,
		$php_ext
	) {
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->request = $request;
		$this->auth = $auth;
		$this->pagination = $pagination;
		$this->event_access = $event_access;
		$this->event_query = $event_query;
		$this->comment_service = $comment_service;
		$this->feed_service = $feed_service;
		$this->calendar_link = $calendar_link;
		$this->event_display = $event_display;
		$this->calendar_add = $calendar_add;
		$this->share = $share;
		$this->geo_proxy = $geo_proxy;
		$this->event_form = $event_form;
		$this->event_manager = $event_manager;
		$this->rsvp = $rsvp;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		$this->user->add_lang_ext('vinny/calendar', 'common');
	}

	public function index()
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->assign_breadcrumbs('EVENT_CALENDAR', $this->helper->route('vinny_calendar_controller'));

		$events = [];
		foreach ($this->event_query->get_public_calendar_events() as $row)
		{
			$events[] = [
				'title' => html_entity_decode($row['title']),
				'start' => $this->format_fullcalendar_value((int) $row['start_at']),
				'end' => $this->format_fullcalendar_value((int) $row['end_at']),
				'url' => $this->calendar_link->route('vinny_calendar_view', $row, ['id' => (int) $row['event_id']]),
				'backgroundColor' => '#' . ($row['cat_color'] ?: '3788d8'),
				'borderColor' => '#' . ($row['cat_color'] ?: '3788d8'),
				'icon' => $row['cat_icon'] ?: 'fa-calendar',
			];
		}

		$user_dateformat = !empty($this->user->data['user_dateformat']) ? $this->user->data['user_dateformat'] : ($this->config['default_dateformat'] ?? 'Y-m-d H:i');
		// Remove escaped characters to avoid false positives (e.g., \h, \d, \e, \a)
		$clean_format = preg_replace('/\\\\./', '', $user_dateformat);
		$is_12hour = (
			(strpos($clean_format, 'a') !== false ||
			strpos($clean_format, 'A') !== false ||
			strpos($clean_format, 'g') !== false ||
			strpos($clean_format, 'h') !== false) &&
			strpos($clean_format, 'H') === false &&
			strpos($clean_format, 'G') === false
		);

		$this->template->assign_vars([
			'U_CREATE_EVENT' => $this->auth->acl_get('u_eventboard_create') ? $this->helper->route('vinny_calendar_create') : '',
			'U_MY_EVENTS' => $this->helper->route('vinny_calendar_my_events'),
			'U_MY_RSVPS' => $this->helper->route('vinny_calendar_my_rsvps'),
			'U_VIEW_ALL' => $this->helper->route('vinny_calendar_upcoming'),
			'U_FEED_EVENTS' => $this->helper->route('vinny_calendar_feed'),
			'U_ICAL' => $this->helper->route('vinny_calendar_ical'),
			'S_FEED_ENABLED' => (int) ($this->config['vinny_calendar_enable_feed'] ?? 0),
			'S_ALLOW_COMMENTS' => (int) ($this->config['vinny_calendar_allow_comments'] ?? 0),
			'CALENDAR_EVENTS_JSON' => json_encode($events),
			'S_FC_12HR' => $is_12hour,
		]);

		return $this->helper->render('event_calendar.html', $this->user->lang('EVENT_CALENDAR'));
	}

	public function upcoming()
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->ensure_content_helpers();
		$this->assign_breadcrumbs('COMING_UP', $this->helper->route('vinny_calendar_upcoming'));

		$start = $this->request->variable('start', 0);
		$per_page = 10;
		$total = $this->event_query->count_upcoming_public_events();

		foreach ($this->event_query->get_upcoming_public_events($per_page, $start) as $row)
		{
			$this->template->assign_block_vars('events', $this->build_list_event_vars($row));
		}

		foreach ($this->event_query->get_category_filters(true) as $category)
		{
			$this->template->assign_block_vars('categories', [
				'NAME' => $category['cat_name'],
				'ICON' => $category['cat_icon'],
				'COLOR' => ltrim($category['cat_color'], '#'),
				'COUNT' => (int) $category['event_count'],
				'U_VIEW' => $this->helper->route('vinny_calendar_category', ['id' => (int) $category['cat_id']]),
			]);
		}

		$base_url = $this->helper->route('vinny_calendar_upcoming');
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $per_page, $start);

		$this->template->assign_vars([
			'TOTAL_EVENTS' => $total,
			'PAGE_NUMBER' => $this->build_page_number($total, $per_page, $start),
		]);

		return $this->helper->render('event_upcoming.html', $this->user->lang('COMING_UP'));
	}

	public function category($id)
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->ensure_content_helpers();

		$category = $this->event_query->get_category((int) $id);
		if (!$category)
		{
			trigger_error('CATEGORY_NOT_FOUND');
		}
		$this->assign_breadcrumbs($category['cat_name'], $this->helper->route('vinny_calendar_category', ['id' => (int) $id]), false);

		$start = $this->request->variable('start', 0);
		$per_page = 10;
		$total = $this->event_query->count_public_category_events((int) $id);

		foreach ($this->event_query->get_public_category_events((int) $id, $per_page, $start) as $row)
		{
			$this->template->assign_block_vars('events', $this->build_list_event_vars($row));
		}

		foreach ($this->event_query->get_category_filters(true) as $item)
		{
			$this->template->assign_block_vars('categories', [
				'NAME' => $item['cat_name'],
				'ICON' => $item['cat_icon'],
				'COLOR' => ltrim($item['cat_color'], '#'),
				'COUNT' => (int) $item['event_count'],
				'S_ACTIVE' => ((int) $item['cat_id'] === (int) $id),
				'U_VIEW' => $this->helper->route('vinny_calendar_category', ['id' => (int) $item['cat_id']]),
			]);
		}

		$base_url = $this->helper->route('vinny_calendar_category', ['id' => (int) $id]);
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $per_page, $start);

		$this->template->assign_vars([
			'CAT_NAME' => $category['cat_name'],
			'CAT_DESC' => $category['cat_desc'],
			'CAT_COLOR' => ltrim($category['cat_color'], '#'),
			'CAT_ICON' => $category['cat_icon'],
			'TOTAL_EVENTS' => $total,
			'PAGE_NUMBER' => $this->build_page_number($total, $per_page, $start),
		]);

		return $this->helper->render('event_category_view.html', $category['cat_name']);
	}

	public function my_events()
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->guard_login();
		$this->assign_breadcrumbs('MY_EVENTS', $this->helper->route('vinny_calendar_my_events'));

		$completed = (bool) $this->request->variable('completed', 0);
		$start = $this->request->variable('start', 0);
		$per_page = 10;
		$user_id = (int) $this->user->data['user_id'];
		$stats = $this->event_query->get_owned_event_stats($user_id);
		$total = $this->event_query->count_owned_events($user_id, $completed);

		foreach ($this->event_query->get_owned_events($user_id, $completed, $per_page, $start) as $row)
		{
			$this->template->assign_block_vars('events', [
				'TITLE' => $row['title'],
				'CAT_COLOR' => ltrim($row['cat_color'], '#'),
				'CAT_ICON' => $row['cat_icon'],
				'DATE_FULL' => $this->user->format_date($row['start_at'], 'M j, Y'),
				'TIME' => $this->user->format_date($row['start_at'], $this->get_time_format()),
				'NUM_PARTICIPANTS' => (int) ($row['num_participants'] ?? 0),
				'MAX_PARTICIPANTS' => (int) ($row['max_participants'] ?? 0),
				'U_VIEW' => $this->calendar_link->route('vinny_calendar_view', $row, ['id' => (int) $row['event_id']]),
				'U_EDIT' => $this->calendar_link->route('vinny_calendar_edit', $row, ['id' => (int) $row['event_id']]),
				'U_DELETE' => $this->calendar_link->route('vinny_calendar_delete', $row, ['id' => (int) $row['event_id']]),
			]);
		}

		$base_url = $this->helper->route('vinny_calendar_my_events', ['completed' => $completed ? 1 : 0]);
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $per_page, $start);

		$this->template->assign_vars([
			'ACTIVE_EVENTS_COUNT' => (int) $stats['active'],
			'TOTAL_SIGNUPS_COUNT' => (int) $stats['signups'],
			'TOTAL_CREATED_EVENTS_COUNT' => (int) $stats['created'],
			'TOTAL_EVENTS' => $total,
			'PAGE_NUMBER' => $this->build_page_number($total, $per_page, $start),
			'S_SHOW_COMPLETED' => $completed,
			'U_CREATE_EVENT' => $this->helper->route('vinny_calendar_create'),
			'U_MY_EVENTS_ACTIVE' => $this->helper->route('vinny_calendar_my_events', ['completed' => 0]),
			'U_MY_EVENTS_COMPLETED' => $this->helper->route('vinny_calendar_my_events', ['completed' => 1]),
		]);

		return $this->helper->render('event_my_events.html', $this->user->lang('MY_EVENTS'));
	}

	public function my_rsvps()
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->guard_login();
		$this->assign_breadcrumbs('MY_RSVPS', $this->helper->route('vinny_calendar_my_rsvps'));

		$start = $this->request->variable('start', 0);
		$per_page = 10;
		$user_id = (int) $this->user->data['user_id'];
		$total = $this->event_query->count_rsvp_events($user_id);

		$form_key = 'vinny_calendar_event_action';
		add_form_key($form_key);

		foreach ($this->event_query->get_rsvp_events($user_id, $per_page, $start) as $row)
		{
			$this->template->assign_block_vars('events', [
				'TITLE' => $row['title'],
				'CAT_COLOR' => ltrim($row['cat_color'], '#'),
				'CAT_ICON' => $row['cat_icon'],
				'DATE_FULL' => $this->user->format_date($row['start_at'], 'M j, Y'),
				'TIME' => $this->user->format_date($row['start_at'], $this->get_time_format()),
				'U_VIEW' => $this->calendar_link->route('vinny_calendar_view', $row, ['id' => (int) $row['event_id']]),
				'U_LEAVE' => $this->calendar_link->route('vinny_calendar_leave', $row, ['id' => (int) $row['event_id']]),
			]);
		}

		$base_url = $this->helper->route('vinny_calendar_my_rsvps');
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $per_page, $start);

		$this->template->assign_vars([
			'TOTAL_RSVPS_COUNT' => $this->event_query->count_user_rsvps($user_id),
			'TOTAL_EVENTS' => $total,
			'PAGE_NUMBER' => $this->build_page_number($total, $per_page, $start),
		]);

		return $this->helper->render('event_my_rsvps.html', $this->user->lang('MY_RSVPS'));
	}

	public function view($id)
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->ensure_posting_helpers();

		$event = $this->event_query->get_event_with_details((int) $id);
		if (!$event)
		{
			trigger_error('EVENT_NOT_FOUND');
		}
		$this->assign_breadcrumbs($event['title'], $this->calendar_link->route('vinny_calendar_view', $event, ['id' => (int) $event['event_id']]), false);

		$this->assert_event_visible($event);

		$user_id = (int) $this->user->data['user_id'];
		$is_owner = ((int) $event['user_id'] === $user_id);
		$has_joined = $this->rsvp->has_joined((int) $event['event_id'], $user_id);
		$total_participants = $this->rsvp->count_participants((int) $event['event_id']);
		$is_completed = ((int) $event['start_at'] <= time());
		$can_join = !$is_owner && !$has_joined && !$is_completed && $user_id !== ANONYMOUS;
		if ((int) $event['max_participants'] > 0 && $total_participants >= (int) $event['max_participants'])
		{
			$can_join = false;
		}

		$form_key = 'vinny_calendar_event_action';
		add_form_key($form_key);
		generate_smilies('inline', 0);

		foreach ($this->event_query->get_event_participants((int) $event['event_id']) as $row)
		{
			$this->template->assign_block_vars('participants', [
				'USER_FULL' => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
			]);
		}

		foreach ($this->comment_service->get_comments_for_event((int) $event['event_id']) as $row)
		{
			$comment_text = generate_text_for_edit($row['message'], $row['uid'] ?? '', $row['options'] ?? 7);
			$this->template->assign_block_vars('comments', [
				'ID' => (int) $row['comment_id'],
				'AUTHOR' => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'AUTHOR_NAME' => $row['username'],
				'DATE' => $this->user->format_date($row['created_at']),
				'MESSAGE' => generate_text_for_display($row['message'], $row['uid'] ?? '', $row['bitfield'] ?? '', $row['options'] ?? 7),
				'QUOTE_TEXT' => $comment_text['text'],
				'S_CAN_DELETE' => ($row['user_id'] == $user_id) || $this->can_manage_event($event),
				'U_DELETE' => $this->calendar_link->private_route('vinny_calendar_delete_comment', (int) $row['comment_id'], (int) $event['visibility'], $event['access_token'], ['id' => (int) $row['comment_id']]),
			]);
		}

		$event_url = $this->calendar_link->absolute_route(generate_board_url(), 'vinny_calendar_view', $event, ['id' => (int) $event['event_id']]);
		$event_location = $this->event_display->is_online($event) ? $this->user->lang('EVENT_ONLINE') : (string) $event['location'];
		$event_plain_description = $this->event_display->plain_text($event['description'], $event['desc_uid'], $event['desc_bitfield'], $event['desc_options']);
		$calendar_targets = $this->calendar_add->build_targets($event, $event_url, $event_plain_description, $event_location);
		$share_targets = $this->share->build_targets($event_url, (string) $event['title']);

		$this->template->assign_vars([
			'EVENT_TITLE' => $event['title'],
			'CAT_NAME' => $event['cat_name'],
			'CAT_COLOR' => ltrim($event['cat_color'], '#'),
			'CAT_ICON' => $event['cat_icon'],
			'EVENT_START_DATE' => $this->user->format_date($event['start_at']),
			'EVENT_END_DATE' => $this->user->format_date($event['end_at']),
			'S_IS_ONLINE' => $this->event_display->is_online($event),
			'EVENT_LOCATION' => $event['location'],
			'EVENT_DESCRIPTION' => generate_text_for_display($event['description'], $event['desc_uid'], $event['desc_bitfield'], $event['desc_options']),
			'EVENT_MAP_IMAGE' => $event['map_image'],
			'TOTAL_PARTICIPANTS' => $total_participants,
			'MAX_PARTICIPANTS' => (int) $event['max_participants'],
			'S_ALLOW_COMMENTS' => (int) ($this->config['vinny_calendar_allow_comments'] ?? 0),
			'S_USER_LOGGED_IN' => ($user_id !== ANONYMOUS),
			'S_IS_OWNER' => $is_owner,
			'S_HAS_JOINED' => $has_joined,
			'S_CAN_JOIN' => $can_join,
			'S_IS_COMPLETED' => $is_completed,
			'S_CAN_COMMENT' => ($user_id !== ANONYMOUS && !$is_completed),
			'S_COMMENTS_CLOSED' => $is_completed,
			'ORGANIZER_FULL' => get_username_string('full', $event['user_id'], $event['username'], $event['user_colour']),
			'EVENT_CREATED_AT' => $this->user->format_date($event['created_at']),
			'U_JOIN_EVENT' => $this->calendar_link->route('vinny_calendar_join', $event, ['id' => (int) $event['event_id']]),
			'U_LEAVE_EVENT' => $this->calendar_link->route('vinny_calendar_leave', $event, ['id' => (int) $event['event_id']]),
			'U_EDIT' => $this->can_manage_event($event) ? $this->calendar_link->route('vinny_calendar_edit', $event, ['id' => (int) $event['event_id']]) : '',
			'U_DELETE' => $this->can_manage_event($event) ? $this->calendar_link->route('vinny_calendar_delete', $event, ['id' => (int) $event['event_id']]) : '',
			'U_ACTION_COMMENT' => $this->calendar_link->route('vinny_calendar_comment', $event, ['id' => (int) $event['event_id']]),
			'U_LOGIN_LOGOUT' => append_sid($this->root_path . 'ucp.' . $this->php_ext, 'mode=login'),
			'S_SMILIES_ALLOWED' => true,
			'S_BBCODE_ALLOWED' => true,
			'S_BBCODE_QUOTE' => true,
			'S_BBCODE_IMG' => true,
			'S_LINKS_ALLOWED' => true,
			'S_BBCODE_FLASH' => false,
			'U_ADD_GOOGLE' => $calendar_targets['google'],
			'U_ADD_OUTLOOK' => $calendar_targets['outlook'],
			'U_ADD_YAHOO' => $calendar_targets['yahoo'],
			'U_SHARE_WHATSAPP' => $share_targets['whatsapp'],
			'U_SHARE_FACEBOOK' => $share_targets['facebook'],
			'U_SHARE_TWITTER' => $share_targets['twitter'],
			'U_SHARE_TELEGRAM' => $share_targets['telegram'],
			'U_SHARE_COPY' => $share_targets['copy'],
		]);

		return $this->helper->render('event_view.html', $event['title']);
	}

	public function create()
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->guard_login();
		$this->assign_breadcrumbs('EVENT_CREATE', $this->helper->route('vinny_calendar_create'));

		if (!$this->auth->acl_get('u_eventboard_create'))
		{
			trigger_error('NOT_AUTHORISED');
		}

		$categories = $this->event_query->get_category_list();
		if (empty($categories))
		{
			trigger_error('NO_ENTRIES');
		}

		$form_key = 'vinny_calendar_event';
		add_form_key($form_key);
		$this->ensure_posting_helpers();

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error('FORM_INVALID');
			}

			$event = $this->event_manager->create_event($this->event_form->build_create_payload());
			redirect($this->calendar_link->route('vinny_calendar_view', $event, ['id' => (int) $event['event_id']]));
		}

		$this->assign_category_options($categories, 0);
		$this->assign_form_defaults([
			'U_ACTION' => $this->helper->route('vinny_calendar_create'),
			'EVENT_SUBJECT' => $this->request->variable('event_subject', '', true),
			'EVENT_START' => $this->request->variable('event_start', ''),
			'EVENT_END' => $this->request->variable('event_end', ''),
			'EVENT_LOCATION' => $this->request->variable('event_location', '', true),
			'EVENT_DESC' => $this->request->variable('event_desc', '', true),
			'EVENT_LAT' => $this->request->variable('event_lat', 0.0),
			'EVENT_LNG' => $this->request->variable('event_lng', 0.0),
			'EVENT_ATTENDEES_LIMIT' => $this->request->variable('event_attendees_limit', 0),
			'EVENT_CATEGORY' => $this->request->variable('event_category', 0),
		]);

		return $this->helper->render('event_create.html', $this->user->lang('EVENT_CREATE'));
	}

	public function edit($id)
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->guard_login();
		$this->ensure_posting_helpers();

		$event = $this->event_query->get_event_basic((int) $id);
		if (!$event)
		{
			trigger_error('EVENT_NOT_FOUND');
		}
		$this->assign_breadcrumbs('EDIT_EVENT', $this->calendar_link->route('vinny_calendar_edit', $event, ['id' => (int) $event['event_id']]));

		if (!$this->can_manage_event($event))
		{
			trigger_error('NOT_AUTHORISED');
		}

		$form_key = 'vinny_calendar_event';
		add_form_key($form_key);

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error('FORM_INVALID');
			}

			$updated = $this->event_manager->update_event((int) $id, $this->event_form->build_update_payload($event));
			redirect($this->calendar_link->route('vinny_calendar_view', $updated, ['id' => (int) $updated['event_id']]));
		}

		$desc = $this->event_display->editable_text($event['description'], $event['desc_uid'], $event['desc_options']);
		$this->assign_category_options($this->event_query->get_category_list(), (int) $event['cat_id']);
		$this->assign_form_defaults([
			'U_ACTION' => $this->calendar_link->route('vinny_calendar_edit', $event, ['id' => (int) $event['event_id']]),
			'EVENT_SUBJECT' => $event['title'],
			'EVENT_START' => $this->format_flatpickr_value((int) $event['start_at']),
			'EVENT_END' => $this->format_flatpickr_value((int) $event['end_at']),
			'EVENT_LOCATION' => $event['location'],
			'EVENT_DESC' => $desc,
			'EVENT_LAT' => $event['lat'],
			'EVENT_LNG' => $event['lng'],
			'EVENT_ATTENDEES_LIMIT' => ((int) $event['max_participants'] > 0) ? (int) $event['max_participants'] : '',
			'EVENT_CATEGORY' => (int) $event['cat_id'],
			'S_LIMIT_ENABLED' => ((int) $event['max_participants'] > 0),
			'S_PUBLIC_ENABLED' => ((int) $event['visibility'] === 0),
			'S_FORMAT_IN_PERSON' => !$this->event_display->is_online($event),
			'S_FORMAT_ONLINE' => $this->event_display->is_online($event),
			'EVENT_MAP_IMAGE_SRC' => $event['map_image'] ? 'images/vinny_calendar_img/' . $event['map_image'] : '',
		]);

		return $this->helper->render('event_edit.html', $this->user->lang('EDIT_EVENT'));
	}

	public function delete($id)
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->guard_login();

		$event = $this->event_query->get_event_for_redirect((int) $id);
		if (!$event)
		{
			trigger_error('EVENT_NOT_FOUND');
		}

		if (!$this->can_manage_event($event))
		{
			trigger_error('NOT_AUTHORISED');
		}

		if (confirm_box(true))
		{
			$this->event_manager->delete_event((int) $id);

			$redirect_url = $this->helper->route('vinny_calendar_controller');
			$referer = (string) $this->request->header('Referer');
			if (strpos($referer, 'my-events') !== false)
			{
				$redirect_url = $this->helper->route('vinny_calendar_my_events');
			}

			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send([
					'MESSAGE_TITLE' => $this->user->lang('INFORMATION'),
					'MESSAGE_TEXT' => $this->user->lang('EVENT_DELETED') . '<br /><br /><a href="' . $redirect_url . '">' . $this->user->lang('RETURN_TO_MY_EVENTS') . '</a>',
				]);
			}

			redirect($redirect_url);
		}

		confirm_box(false, $this->user->lang('CONFIRM_DELETE_EVENT'), build_hidden_fields([
			'id' => (int) $id,
			't' => $this->calendar_link->current_access_token(),
		]));
	}

	public function join($id)
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->guard_login();
		$this->check_action_form();

		$event = $this->event_query->get_event_for_join((int) $id);
		if (!$event)
		{
			trigger_error('EVENT_NOT_FOUND');
		}

		$this->assert_event_visible($event);
		if ((int) $event['start_at'] <= time())
		{
			trigger_error('EVENT_ENDED');
		}

		if ((int) $event['user_id'] === (int) $this->user->data['user_id'])
		{
			trigger_error('EVENT_OWNER_CANNOT_RSVP');
		}

		$this->rsvp->join($event, (int) $this->user->data['user_id']);
		redirect($this->calendar_link->route('vinny_calendar_view', $event, ['id' => (int) $event['event_id']]));
	}

	public function leave($id)
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->guard_login();
		$this->check_action_form();

		$event = $this->event_query->get_event_for_join((int) $id);
		if (!$event)
		{
			trigger_error('EVENT_NOT_FOUND');
		}

		$this->assert_event_visible($event);
		$this->rsvp->leave((int) $id, (int) $this->user->data['user_id']);
		redirect($this->calendar_link->route('vinny_calendar_view', $event, ['id' => (int) $event['event_id']]));
	}

	public function feed()
	{
		$this->guard_enabled();
		$this->guard_view_permission();

		if (!(int) ($this->config['vinny_calendar_enable_feed'] ?? 0))
		{
			trigger_error('NOT_AUTHORISED');
		}

		$this->ensure_content_helpers();

		$board_url = generate_board_url();
		$feed = $this->feed_service->build_rss(
			$this->event_query->get_public_feed_events(30),
			$this->config['sitename'] . ' - ' . $this->user->lang('EVENT_CALENDAR'),
			$this->helper->route('vinny_calendar_feed'),
			$this->user->lang('EVENT_CALENDAR'),
			$board_url,
			function ($event)
			{
				return $this->calendar_link->route('vinny_calendar_view', $event, ['id' => (int) $event['event_id']]);
			}
		);

		return new Response($feed, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
	}

	public function ical()
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->ensure_content_helpers();

		$events = $this->event_query->get_public_ical_events();
		$ical = $this->feed_service->build_ical(
			$events,
			$this->config['sitename'] . ' - ' . $this->user->lang('EVENT_CALENDAR'),
			$this->config['board_timezone'] ?? 'UTC',
			$this->request->server('HTTP_HOST', 'localhost'),
			function ($event)
			{
				return $this->calendar_link->absolute_route(generate_board_url(), 'vinny_calendar_view', $event, ['id' => (int) $event['event_id']]);
			}
		);

		return new Response($ical, 200, [
			'Content-Type' => 'text/calendar; charset=UTF-8',
			'Content-Disposition' => 'attachment; filename="calendar.ics"',
		]);
	}

	public function ical_event($id)
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->ensure_content_helpers();

		$event = $this->event_query->get_event_with_details((int) $id);
		if (!$event)
		{
			trigger_error('EVENT_NOT_FOUND');
		}

		$this->assert_event_visible($event);

		$ical = $this->feed_service->build_ical(
			[$event],
			$event['title'],
			$this->config['board_timezone'] ?? 'UTC',
			$this->request->server('HTTP_HOST', 'localhost'),
			function ($row)
			{
				return $this->calendar_link->absolute_route(generate_board_url(), 'vinny_calendar_view', $row, ['id' => (int) $row['event_id']]);
			}
		);

		$filename = preg_replace('/[^a-z0-9_\-]+/i', '-', $event['title']) . '.ics';
		return new Response($ical, 200, [
			'Content-Type' => 'text/calendar; charset=UTF-8',
			'Content-Disposition' => 'attachment; filename="' . $filename . '"',
		]);
	}

	public function proxy()
	{
		$this->guard_enabled();
		$this->guard_login();

		if (!$this->auth->acl_get('u_eventboard_create'))
		{
			return new JsonResponse(['features' => []], 403);
		}

		return new JsonResponse($this->geo_proxy->autocomplete($this->request->variable('text', '', true)));
	}

	public function submit_comment($id)
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->guard_login();
		$this->ensure_posting_helpers();
		$this->check_action_form();

		$event = $this->event_query->get_event_for_comment((int) $id);
		if (!$event)
		{
			trigger_error('EVENT_NOT_FOUND');
		}

		$this->assert_event_visible($event);
		if ((int) $event['start_at'] <= time())
		{
			trigger_error('EVENT_COMMENTS_CLOSED');
		}

		$message = trim($this->request->variable('comment_text', '', true));
		if ($message === '')
		{
			trigger_error('MESSAGE_EMPTY');
		}

		$uid = $bitfield = '';
		$options = 7;
		generate_text_for_storage($message, $uid, $bitfield, $options, true, true, true);

		$comment_id = $this->comment_service->create_comment((int) $id, (int) $this->user->data['user_id'], $message, $uid, $bitfield, $options);
		$this->comment_service->notify_new_comment($event, $comment_id, (int) $this->user->data['user_id']);

		redirect($this->calendar_link->route('vinny_calendar_view', $event, ['id' => (int) $event['event_id']]) . '#comments');
	}

	public function delete_comment($id)
	{
		$this->guard_enabled();
		$this->guard_view_permission();
		$this->guard_login();

		$comment = $this->comment_service->get_comment_with_event((int) $id);
		if (!$comment)
		{
			trigger_error('COMMENT_NOT_FOUND');
		}

		$can_delete = ((int) $comment['user_id'] === (int) $this->user->data['user_id'])
			|| $this->auth->acl_get('a_')
			|| $this->auth->acl_get('m_')
			|| ((int) $comment['event_owner_id'] === (int) $this->user->data['user_id']);

		if (!$can_delete)
		{
			trigger_error('NOT_AUTHORISED');
		}

		$this->comment_service->delete_comment((int) $id);
		redirect($this->calendar_link->private_route('vinny_calendar_view', (int) $comment['event_id'], (int) $comment['visibility'], $comment['access_token'], ['id' => (int) $comment['event_id']]) . '#comments');
	}

	protected function assign_form_defaults(array $vars)
	{
		$this->template->assign_vars(array_merge([
			'S_FP_THEME' => $this->config['vinny_calendar_fp_theme'] ?? 'default',
			'S_FP_LANGUAGE' => $this->config['vinny_calendar_fp_language'] ?? 'default',
			'S_FP_TIME_24HR' => (int) ($this->config['vinny_calendar_fp_time_24hr'] ?? 1),
			'S_FP_DATE_FORMAT' => $this->get_flatpickr_alt_format(),
			'U_GEO_PROXY' => $this->helper->route('vinny_calendar_geo_proxy'),
			'S_GEOAPIFY_ENABLED' => ((string) ($this->config['vinny_calendar_geoapify_key'] ?? '') !== ''),
			'VINNY_CALENDAR_MAP_LANG' => (string) ($this->config['vinny_calendar_map_lang'] ?? 'en'),
			'S_BBCODE_ALLOWED' => true,
			'S_SMILIES_ALLOWED' => true,
			'S_LINKS_ALLOWED' => true,
			'S_BBCODE_IMG' => true,
			'S_BBCODE_URL' => true,
			'S_BBCODE_FLASH' => false,
			'S_LIMIT_ENABLED' => false,
			'S_PUBLIC_ENABLED' => true,
			'S_FORMAT_IN_PERSON' => true,
			'S_FORMAT_ONLINE' => false,
		], $vars));

		generate_smilies('inline', 0);
	}

	protected function assign_category_options(array $categories, $selected_id)
	{
		foreach ($categories as $category)
		{
			$this->template->assign_block_vars('categories', [
				'ID' => (int) $category['cat_id'],
				'NAME' => $category['cat_name'],
				'ICON' => $category['cat_icon'],
				'COLOR' => ltrim($category['cat_color'], '#'),
				'S_SELECTED' => ((int) $selected_id === (int) $category['cat_id']),
			]);
		}
	}

	protected function build_list_event_vars(array $row)
	{
		return [
			'TITLE' => $row['title'],
			'DESCRIPTION' => $this->event_display->plain_text($row['description'], $row['desc_uid'], $row['desc_bitfield'], $row['desc_options']),
			'LOCATION' => $row['location'] ?: $this->user->lang('EVENT_ONLINE'),
			'CAT_NAME' => $row['cat_name'],
			'CAT_COLOR' => ltrim($row['cat_color'], '#'),
			'CAT_ICON' => $row['cat_icon'],
			'NUM_PARTICIPANTS' => (int) ($row['num_participants'] ?? 0),
			'DATE_FULL' => $this->user->format_date($row['start_at']),
			'S_IS_ONLINE' => $this->event_display->is_online($row),
			'U_VIEW' => $this->calendar_link->route('vinny_calendar_view', $row, ['id' => (int) $row['event_id']]),
		];
	}

	protected function can_manage_event(array $event)
	{
		return $this->auth->acl_get('a_')
			|| $this->auth->acl_get('m_')
			|| ((int) $event['user_id'] === (int) $this->user->data['user_id']);
	}

	protected function assert_event_visible(array $event)
	{
		if (!$this->event_access->can_view_event($event, (int) $this->user->data['user_id'], $this->calendar_link->current_access_token()))
		{
			trigger_error('EVENT_ACCESS_DENIED');
		}
	}

	protected function guard_enabled()
	{
		if (!(int) ($this->config['vinny_calendar_enable'] ?? 0))
		{
			trigger_error('EVENTBOARD_DISABLED');
		}
	}

	protected function guard_view_permission()
	{
		if (!$this->auth->acl_get('u_eventboard_view'))
		{
			trigger_error('NOT_AUTHORISED');
		}
	}

	protected function guard_login()
	{
		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			login_box();
		}
	}

	protected function ensure_content_helpers()
	{
		include_once($this->root_path . 'includes/functions_content.' . $this->php_ext);
	}

	protected function ensure_posting_helpers()
	{
		$this->ensure_content_helpers();
		include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		$this->user->add_lang('posting');
		$this->user->add_lang('viewtopic');
	}

	protected function check_action_form()
	{
		if (!check_form_key('vinny_calendar_event_action'))
		{
			trigger_error('FORM_INVALID');
		}
	}

	protected function get_time_format()
	{
		return (($this->config['vinny_calendar_fc_time_format'] ?? '24') === '12') ? 'g:i A' : 'H:i';
	}

	protected function get_flatpickr_alt_format()
	{
		$format = trim((string) ($this->config['vinny_calendar_fp_date_format'] ?? 'd/m/Y H:i'));
		$time_format = ((int) ($this->config['vinny_calendar_fp_time_24hr'] ?? 1) === 1) ? 'H:i' : 'h:i K';

		if ($format === '')
		{
			return 'd/m/Y ' . $time_format;
		}

		$updated = preg_replace('/(?:H|G|h|g):i(?:\s*(?:K|A))?/', $time_format, $format, 1);

		return ($updated === $format) ? trim($format . ' ' . $time_format) : $updated;
	}

	protected function format_flatpickr_value($timestamp)
	{
		$date = new \DateTimeImmutable('@' . (int) $timestamp);

		return $date->setTimezone($this->get_user_timezone())->format('Y-m-d H:i');
	}

	protected function format_fullcalendar_value($timestamp)
	{
		$date = new \DateTimeImmutable('@' . (int) $timestamp);

		return $date->setTimezone($this->get_user_timezone())->format('Y-m-d\TH:i:s');
	}

	protected function get_user_timezone()
	{
		if (isset($this->user->timezone) && $this->user->timezone instanceof \DateTimeZone)
		{
			return $this->user->timezone;
		}

		try
		{
			return new \DateTimeZone((string) ($this->config['board_timezone'] ?? date_default_timezone_get()));
		}
		catch (\Exception $e)
		{
			return new \DateTimeZone(date_default_timezone_get());
		}
	}

	protected function build_page_number($total, $per_page, $start)
	{
		if (!$total)
		{
			return '';
		}

		$current = (int) floor($start / $per_page) + 1;
		$pages = (int) ceil($total / $per_page);

		return $current . ' / ' . $pages;
	}

	protected function assign_breadcrumbs($label, $url, $translate = true)
	{
		$this->template->assign_block_vars('navlinks', [
			'FORUM_NAME' => $this->user->lang('EVENT_CALENDAR'),
			'U_VIEW_FORUM' => $this->helper->route('vinny_calendar_controller'),
		]);

		if ($label !== 'EVENT_CALENDAR')
		{
			$this->template->assign_block_vars('navlinks', [
				'FORUM_NAME' => $translate ? $this->user->lang($label) : $label,
				'U_VIEW_FORUM' => $url,
			]);
		}
	}
}
