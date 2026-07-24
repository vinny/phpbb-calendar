<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\notification\type;

class event_reminder extends \phpbb\notification\type\base
{
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\config\config */
	protected $config;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\user $user, \phpbb\auth\auth $auth, $phpbb_root_path, $php_ext, $user_notifications_table, \phpbb\config\config $config)
	{
		parent::__construct($db, $language, $user, $auth, $phpbb_root_path, $php_ext, $user_notifications_table);
		$this->config = $config;
	}

	public function set_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	public function get_type()
	{
		return 'vinny.calendar.notification.type.event_reminder';
	}

	public static $notification_option = [
		'lang'	=> 'VINNY_CALENDAR_NOTIFICATION_EVENT_REMINDER',
		'group'	=> 'NOTIFICATION_GROUP_EVENTS',
	];

	public function is_available()
	{
		return !empty($this->config['vinny_calendar_enable']) && !empty($this->config['vinny_calendar_reminder_minutes']);
	}

	public static function get_item_id($data)
	{
		return $data['event_id'];
	}

	public static function get_item_parent_id($data)
	{
		return 0;
	}

	public function find_users_for_notification($data, $options = [])
	{
		$users = [(int) $data['organizer_id']];

		if (!empty($data['notify_users']) && is_array($data['notify_users']))
		{
			$users = array_merge($users, $data['notify_users']);
		}

		$users = array_unique(array_map('intval', $users));
		$users = array_diff($users, [ANONYMOUS]);

		return $this->check_user_notification_options($users, $options);
	}

	public function users_to_query()
	{
		return [];
	}

	public function get_title()
	{
		return $this->language->lang(
			'VINNY_CALENDAR_NOTIFICATION_EVENT_REMINDER_TITLE',
			$this->get_data('event_title'),
			(int) $this->get_data('lead_minutes')
		);
	}

	public function get_url()
	{
		$params = ['id' => (int) $this->get_data('event_id')];

		if ((int) $this->get_data('event_visibility') === 1 && $this->get_data('event_access_token') !== '')
		{
			$params['t'] = $this->get_data('event_access_token');
		}

		return $this->helper->route('vinny_calendar_view', $params);
	}

	/** @var \vinny\calendar\service\calendar_link */
	protected $calendar_link;

	public function set_calendar_link(\vinny\calendar\service\calendar_link $calendar_link)
	{
		$this->calendar_link = $calendar_link;
	}

	public function get_email_template()
	{
		return '@vinny_calendar/event_reminder';
	}

	public function get_email_template_variables()
	{
		$url = $this->get_url();
		if ($this->calendar_link)
		{
			$url = $this->calendar_link->absolute_url(generate_board_url(), $url);
		}
		else
		{
			$url = generate_board_url() . '/' . ltrim($url, './');
		}

		return [
			'EVENT_TITLE'  => html_entity_decode(censor_text($this->get_data('event_title')), ENT_COMPAT),
			'LEAD_MINUTES' => (int) $this->get_data('lead_minutes'),
			'U_EVENT'      => $url,
		];
	}

	public function create_insert_array($data, $pre_create_data = [])
	{
		parent::create_insert_array($data, $pre_create_data);

		$this->set_data('event_id', (int) $data['event_id']);
		$this->set_data('event_title', $data['event_title']);
		$this->set_data('event_visibility', (int) $data['event_visibility']);
		$this->set_data('event_access_token', $data['event_access_token']);
		$this->set_data('organizer_id', (int) $data['organizer_id']);
		$this->set_data('lead_minutes', (int) $data['lead_minutes']);
	}
}
