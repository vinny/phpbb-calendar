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

class new_comment extends \phpbb\notification\type\base
{
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * Constructor
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\user $user, \phpbb\auth\auth $auth, $phpbb_root_path, $php_ext, $user_notifications_table, \phpbb\config\config $config)
	{
		parent::__construct($db, $language, $user, $auth, $phpbb_root_path, $php_ext, $user_notifications_table);
		$this->config = $config;
	}

	public function set_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}



	public function get_type()
	{
		return 'vinny.calendar.notification.type.new_comment';
	}

	public static $notification_option = [
		'lang'  => 'VINNY_CALENDAR_NOTIFICATION_NEW_COMMENT',
		'group' => 'NOTIFICATION_GROUP_EVENTS',
	];

	public function is_available()
	{
		return !empty($this->config['vinny_calendar_enable']) && !empty($this->config['vinny_calendar_allow_comments']);
	}

	public static function get_item_id($data)
	{
		return $data['comment_id'];
	}

	public static function get_item_parent_id($data)
	{
		return $data['event_id'];
	}

	public function find_users_for_notification($data, $options = [])
	{
		$users = [(int) $data['organizer_id']];

		if (isset($data['notify_users']) && is_array($data['notify_users']))
		{
			$users = array_merge($users, $data['notify_users']);
		}

		$users = array_unique($users);
		$users = array_diff($users, [(int) $data['sender_id']]);

		return $this->check_user_notification_options($users, $options);
	}

	public function users_to_query()
	{
		return [(int) $this->get_data('sender_id')];
	}

	public function get_title()
	{
		return $this->language->lang(
			'VINNY_CALENDAR_NOTIFICATION_NEW_COMMENT_TITLE',
			$this->user_loader->get_username($this->get_data('sender_id'), 'no_profile'),
			$this->get_data('event_title')
		);
	}

	public function get_url()
	{
		return $this->helper->route('vinny_calendar_view', ['id' => $this->get_data('event_id')]) . '#comments';
	}

	/** @var \vinny\calendar\service\calendar_link */
	protected $calendar_link;

	public function set_calendar_link(\vinny\calendar\service\calendar_link $calendar_link)
	{
		$this->calendar_link = $calendar_link;
	}

	public function get_email_template()
	{
		return '@vinny_calendar/new_comment';
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

		$username = $this->user_loader->get_username($this->get_data('sender_id'), 'username');

		return [
			'AUTHOR_NAME' => html_entity_decode($username, ENT_COMPAT),
			'EVENT_TITLE' => html_entity_decode(censor_text($this->get_data('event_title')), ENT_COMPAT),
			'U_EVENT'     => $url,
		];
	}

	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('sender_id'), false, true);
	}

	public function create_insert_array($data, $pre_create_data = [])
	{
		parent::create_insert_array($data, $pre_create_data);

		$this->set_data('comment_id', $data['comment_id']);
		$this->set_data('event_id', $data['event_id']);
		$this->set_data('event_title', $data['event_title']);
		$this->set_data('organizer_id', $data['organizer_id']);
		if (isset($data['sender_id']))
		{
			$this->set_data('sender_id', $data['sender_id']);
		}
	}
}
