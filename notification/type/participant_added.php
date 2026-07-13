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

class participant_added extends \phpbb\notification\type\base
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

	/**
	 * Set user loader.
	 *
	 * @param \phpbb\user_loader  $user_loader  User loader object
	 * @return void
	 */
	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	 * Get notification type name.
	 */
	public function get_type()
	{
		return 'vinny.calendar.notification.type.participant_added';
	}

	public static $notification_option = [
		'lang'  => 'VINNY_CALENDAR_NOTIFICATION_PARTICIPANT_ADDED',
		'group' => 'NOTIFICATION_GROUP_EVENTS',
	];

	/**
	 * Is this type available to the current user.
	 */
	public function is_available()
	{
		return !empty($this->config['vinny_calendar_enable']);
	}

	/**
	 * Get the id of the item.
	 */
	public static function get_item_id($data)
	{
		return $data['event_id'];
	}

	/**
	 * Get the id of the parent.
	 */
	public static function get_item_parent_id($data)
	{
		return 0;
	}

	/**
	 * Find the users who want to receive notifications.
	 */
	public function find_users_for_notification($data, $options = [])
	{
		return $this->check_user_notification_options([$data['organizer_id']], $options);
	}

	/**
	 * Users needed to query before this notification can be displayed.
	 */
	public function users_to_query()
	{
		return [(int) $this->get_data('sender_id')];
	}

	/**
	 * Get the title of this notification.
	 */
	public function get_title()
	{
		return $this->language->lang(
			'VINNY_CALENDAR_NOTIFICATION_PARTICIPANT_ADDED_TITLE',
			$this->user_loader->get_username($this->get_data('sender_id'), 'no_profile'),
			$this->get_data('event_title')
		);
	}

	/**
	 * Get the URL to this item.
	 */
	public function get_url()
	{
		return $this->helper->route('vinny_calendar_view', ['id' => $this->get_data('event_id')]);
	}

	/**
	 * Get email template.
	 */
	public function get_email_template()
	{
		return false;
	}

	/**
	 * Get email template variables.
	 */
	public function get_email_template_variables()
	{
		return [];
	}

	/**
	 * Get the user's avatar.
	 */
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('sender_id'), false, true);
	}

	/**
	 * Data needed for notifications.
	 */
	public function create_insert_array($data, $pre_create_data = [])
	{
		parent::create_insert_array($data, $pre_create_data);

		$this->set_data('event_id', $data['event_id']);
		$this->set_data('event_title', $data['event_title']);
		$this->set_data('organizer_id', $data['organizer_id']);
		if (isset($data['sender_id']))
		{
			$this->set_data('sender_id', $data['sender_id']);
		}
	}
}
