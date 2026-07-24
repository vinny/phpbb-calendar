<?php

/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\service;

class comment
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var string */
	protected $table_prefix;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\notification\manager $notification_manager, $table_prefix)
	{
		$this->db = $db;
		$this->notification_manager = $notification_manager;
		$this->table_prefix = $table_prefix;

		if (!defined('EVENTBOARD_EVENTS_TABLE'))
		{
			define('EVENTBOARD_EVENTS_TABLE', $table_prefix . 'eventboard_events');
			define('EVENTBOARD_CATEGORIES_TABLE', $table_prefix . 'eventboard_categories');
			define('EVENTBOARD_PARTICIPANTS_TABLE', $table_prefix . 'eventboard_participants');
			define('EVENTBOARD_COMMENTS_TABLE', $table_prefix . 'eventboard_comments');
		}
	}

	public function get_comments_for_event($event_id)
	{
		$sql = 'SELECT c.*, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height
            FROM ' . EVENTBOARD_COMMENTS_TABLE . ' c
            JOIN ' . USERS_TABLE . ' u ON (c.user_id = u.user_id)
            WHERE c.event_id = ' . (int) $event_id . '
            ORDER BY c.created_at DESC';

		return $this->fetch_all($sql);
	}

	public function create_comment($event_id, $user_id, $message, $uid, $bitfield, $options)
	{
		$sql_ary = [
			'event_id' => (int) $event_id,
			'user_id' => (int) $user_id,
			'message' => $message,
			'uid' => $uid,
			'bitfield' => $bitfield,
			'options' => $options,
			'created_at' => time(),
		];

		$sql = 'INSERT INTO ' . EVENTBOARD_COMMENTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		return (int) $this->db->sql_nextid();
	}

	public function get_event_notify_users($event_id)
	{
		$sql = 'SELECT user_id
            FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . '
            WHERE event_id = ' . (int) $event_id;
		$result = $this->db->sql_query($sql);

		$notify_users = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$notify_users[] = (int) $row['user_id'];
		}

		$this->db->sql_freeresult($result);

		return $notify_users;
	}

	public function notify_new_comment(array $event, $comment_id, $sender_id)
	{
		$this->notification_manager->add_notifications(
			'vinny.calendar.notification.type.new_comment',
			[
				'comment_id' => (int) $comment_id,
				'event_id' => (int) $event['event_id'],
				'event_title' => $event['title'],
				'organizer_id' => (int) $event['user_id'],
				'notify_users' => $this->get_event_notify_users($event['event_id']),
				'sender_id' => (int) $sender_id,
			]
		);
	}

	public function get_comment_with_event($comment_id)
	{
		$sql = 'SELECT c.user_id, c.event_id, e.visibility, e.access_token, e.user_id as event_owner_id
            FROM ' . EVENTBOARD_COMMENTS_TABLE . ' c
            JOIN ' . EVENTBOARD_EVENTS_TABLE . ' e ON (e.event_id = c.event_id)
            WHERE c.comment_id = ' . (int) $comment_id;

		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	public function delete_comment($comment_id)
	{
		$sql = 'DELETE FROM ' . EVENTBOARD_COMMENTS_TABLE . '
            WHERE comment_id = ' . (int) $comment_id;
		$this->db->sql_query($sql);
	}

	protected function fetch_all($sql)
	{
		$rows = [];
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}

		$this->db->sql_freeresult($result);

		return $rows;
	}
}
