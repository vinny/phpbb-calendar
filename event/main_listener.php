<?php

/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \vinny\calendar\service\calendar_link */
	protected $calendar_link;

	/** @var \vinny\calendar\service\event_query */
	protected $event_query;

	/**
	 * Constructor
	 *
	 * @param \phpbb\user $user
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbb\template\template $template
	 * @param \phpbb\config\config $config
	 * @param \phpbb\auth\auth $auth
	 * @param \vinny\calendar\service\calendar_link $calendar_link
	 * @param \vinny\calendar\service\event_query $event_query
	 */
	public function __construct(\phpbb\user $user, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\config\config $config, \phpbb\auth\auth $auth, \vinny\calendar\service\calendar_link $calendar_link, \vinny\calendar\service\event_query $event_query)
	{
		$this->user = $user;
		$this->helper = $helper;
		$this->template = $template;
		$this->config = $config;
		$this->auth = $auth;
		$this->calendar_link = $calendar_link;
		$this->event_query = $event_query;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup' => 'load_language_on_setup',
			'core.page_header' => 'add_page_header_link',
			'core.permissions' => 'add_permissions',
			'core.index_modify_page_title' => 'index_modify_page_title',
		];
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'vinny/calendar',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link($event)
	{
		if (empty($this->config['vinny_calendar_enable']))
		{
			return;
		}

		$feed_enabled = !empty($this->config['vinny_calendar_enable_feed']) && $this->auth->acl_get('u_eventboard_view');
		$feed_url = '';
		if ($feed_enabled)
		{
			$feed_url = $this->calendar_link->absolute_url(generate_board_url(), $this->helper->route('vinny_calendar_feed'));
		}

		$this->template->assign_vars([
			'U_EVENT_CALENDAR' => $this->helper->route('vinny_calendar_controller'),
			'S_CALENDAR_FEED_ENABLED' => $feed_enabled,
			'U_CALENDAR_FEED' => $feed_url,
		]);
	}

	public function add_permissions($event)
	{
		$categories = $event['categories'];
		$categories['eventboard'] = 'ACL_CAT_EVENTBOARD';
		$event['categories'] = $categories;

		$permissions = $event['permissions'];
		$permissions['u_eventboard_view'] = ['lang' => 'ACL_U_EVENTBOARD_VIEW', 'cat' => 'eventboard'];
		$permissions['u_eventboard_create'] = ['lang' => 'ACL_U_EVENTBOARD_CREATE', 'cat' => 'eventboard'];
		$permissions['u_eventboard_delete'] = ['lang' => 'ACL_U_EVENTBOARD_DELETE', 'cat' => 'eventboard'];
		$permissions['u_eventboard_comment'] = ['lang' => 'ACL_U_EVENTBOARD_COMMENT', 'cat' => 'eventboard'];
		$event['permissions'] = $permissions;
	}

	public function index_modify_page_title($event)
	{
		if (empty($this->config['vinny_calendar_enable']) || !$this->auth->acl_get('u_eventboard_view'))
		{
			return;
		}

		$user_id = (int) $this->user->data['user_id'];

		if (!empty($this->config['vinny_calendar_display_occurring']))
		{
			$occurring_events = $this->event_query->get_occurring_public_events($user_id);
			foreach ($occurring_events as $row)
			{
				$this->template->assign_block_vars('occurring_events', [
					'TITLE'  => $row['title'],
					'U_VIEW' => $this->calendar_link->route('vinny_calendar_view', $row, ['id' => (int) $row['event_id']]),
				]);
			}
			$this->template->assign_vars([
				'S_DISPLAY_OCCURRING_EVENTS' => true,
				'S_HAS_OCCURRING_EVENTS'     => !empty($occurring_events),
			]);
		}

		if (!empty($this->config['vinny_calendar_display_upcoming']))
		{
			$upcoming_events = $this->event_query->get_upcoming_public_events(5, 0, $user_id);
			foreach ($upcoming_events as $row)
			{
				$this->template->assign_block_vars('upcoming_events_list', [
					'TITLE'      => $row['title'],
					'U_VIEW'     => $this->calendar_link->route('vinny_calendar_view', $row, ['id' => (int) $row['event_id']]),
					'START_DATE' => $this->user->format_date((int) $row['start_at']),
					'CAT_ICON'   => $row['cat_icon'] ?: 'fa-calendar',
				]);
			}
			$this->template->assign_vars([
				'S_DISPLAY_UPCOMING_EVENTS' => true,
				'S_HAS_UPCOMING_EVENTS'     => !empty($upcoming_events),
			]);
		}

		if (!empty($this->config['vinny_calendar_display_stats']))
		{
			$total_users_string = $this->user->lang('TOTAL_USERS', (int) $this->config['num_users']);
			$total_events = (int) $this->event_query->get_total_events_count();
			$total_events_string = $this->user->lang('TOTAL_EVENTS', $total_events);

			$this->template->assign_var('TOTAL_USERS', $total_users_string . ' &bull; ' . $total_events_string);
		}
	}
}
