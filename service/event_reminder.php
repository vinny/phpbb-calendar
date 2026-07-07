<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GPL-2.0-only
 *
 */

namespace vinny\calendar\service;

class event_reminder
{
	protected $config;
	protected $db;
	protected $notification_manager;
	protected $table_prefix;

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\notification\manager $notification_manager, $table_prefix)
	{
		$this->config = $config;
		$this->db = $db;
		$this->notification_manager = $notification_manager;
		$this->table_prefix = $table_prefix;
	}

	public function is_enabled()
	{
		return !empty($this->config['vinny_calendar_enable']) && (int) ($this->config['vinny_calendar_reminder_minutes'] ?? 0) > 0;
	}

	public function dispatch_due_reminders()
	{
		if (!$this->is_enabled())
		{
			return;
		}

		$lead_minutes = (int) $this->config['vinny_calendar_reminder_minutes'];
		$now = time();
		$window_end = $now + ($lead_minutes * 60);

		$sql = 'SELECT *
			FROM ' . $this->table_prefix . 'eventboard_events
			WHERE start_at > ' . $now . '
				AND start_at <= ' . $window_end . '
				AND reminder_sent_at = 0';
		$result = $this->db->sql_query($sql);

		$events = [];
		$event_ids = [];
		while ($event = $this->db->sql_fetchrow($result))
		{
			$events[(int) $event['event_id']] = $event;
			$event_ids[] = (int) $event['event_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($event_ids))
		{
			return;
		}

		$participants = [];
		$participant_sql = 'SELECT event_id, user_id
			FROM ' . $this->table_prefix . 'eventboard_participants
			WHERE ' . $this->db->sql_in_set('event_id', $event_ids);
		$participant_result = $this->db->sql_query($participant_sql);
		while ($row = $this->db->sql_fetchrow($participant_result))
		{
			$participants[(int) $row['event_id']][] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($participant_result);

		foreach ($events as $event_id => $event)
		{
			$notify_users = isset($participants[$event_id]) ? $participants[$event_id] : [];

			$this->notification_manager->add_notifications(
				'vinny.calendar.notification.type.event_reminder',
				[
					'event_id' => $event_id,
					'event_title' => $event['title'],
					'event_visibility' => (int) $event['visibility'],
					'event_access_token' => $event['access_token'],
					'organizer_id' => (int) $event['user_id'],
					'notify_users' => $notify_users,
					'lead_minutes' => $lead_minutes,
				]
			);
		}

		$update_sql = 'UPDATE ' . $this->table_prefix . 'eventboard_events
			SET reminder_sent_at = ' . $now . '
			WHERE ' . $this->db->sql_in_set('event_id', $event_ids);
		$this->db->sql_query($update_sql);
	}
}
