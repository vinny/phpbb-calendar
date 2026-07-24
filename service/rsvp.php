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

class rsvp
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

	public function has_joined($event_id, $user_id)
	{
		$sql = 'SELECT id
            FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . '
            WHERE event_id = ' . (int) $event_id . '
                AND user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return (bool) $row;
	}

	public function count_participants($event_id)
	{
		$sql = 'SELECT COUNT(id) as total
            FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . '
            WHERE event_id = ' . (int) $event_id;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	public function join(array $event, $user_id)
	{
		if ($this->has_joined($event['event_id'], $user_id))
		{
			return 'already_joined';
		}

		$this->db->sql_transaction('begin');

		$sql = 'SELECT max_participants
            FROM ' . EVENTBOARD_EVENTS_TABLE . '
            WHERE event_id = ' . (int) $event['event_id'];
		$result = $this->db->sql_query($sql);
		$current_event = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$current_event)
		{
			$this->db->sql_transaction('commit');
			return 'not_found';
		}

		$total = $this->count_participants($event['event_id']);
		if ((int) $current_event['max_participants'] > 0 && $total >= (int) $current_event['max_participants'])
		{
			$this->db->sql_transaction('commit');
			return 'full';
		}

		$sql_ary = [
			'event_id' => (int) $event['event_id'],
			'user_id' => (int) $user_id,
			'joined_at' => time(),
		];
		$sql = 'INSERT INTO ' . EVENTBOARD_PARTICIPANTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$total_after_insert = $this->count_participants($event['event_id']);
		if ((int) $current_event['max_participants'] > 0 && $total_after_insert > (int) $current_event['max_participants'])
		{
			$this->db->sql_transaction('rollback');

			return 'full';
		}

		$this->db->sql_transaction('commit');

		$this->notification_manager->add_notifications(
			'vinny.calendar.notification.type.participant_added',
			[
				'event_id' => (int) $event['event_id'],
				'event_title' => $event['title'],
				'organizer_id' => (int) $event['user_id'],
				'sender_id' => (int) $user_id,
			]
		);

		return 'joined';
	}

	public function leave($event_id, $user_id)
	{
		$sql = 'DELETE FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . '
            WHERE event_id = ' . (int) $event_id . '
                AND user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}
}
