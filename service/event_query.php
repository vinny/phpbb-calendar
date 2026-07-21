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

class event_query
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	protected $table_prefix;

	public function __construct(\phpbb\db\driver\driver_interface $db, $table_prefix)
	{
		$this->db = $db;
		$this->table_prefix = $table_prefix;
	}

	public function get_public_calendar_events($user_id = 0)
	{
		$user_id = (int) $user_id;

		if ($user_id > 1)
		{
			$sql = 'SELECT e.event_id, e.title, e.start_at, e.end_at, e.visibility, e.access_token, c.cat_color, c.cat_icon
				FROM ' . $this->table_prefix . 'eventboard_events e
				LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
				LEFT JOIN ' . $this->table_prefix . 'eventboard_participants p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL)';
		}
		else
		{
			$sql = 'SELECT e.event_id, e.title, e.start_at, e.end_at, e.visibility, e.access_token, c.cat_color, c.cat_icon
				FROM ' . $this->table_prefix . 'eventboard_events e
				LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
				WHERE e.visibility = 0';
		}

		return $this->fetch_all($sql);
	}

	public function get_upcoming_public_events($limit, $start = 0, $user_id = 0)
	{
		$user_id = (int) $user_id;

		if ($user_id > 1)
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
					(SELECT COUNT(p.id) FROM ' . $this->table_prefix . 'eventboard_participants p WHERE p.event_id = e.event_id) as num_participants
				FROM ' . $this->table_prefix . 'eventboard_events e
				LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
				LEFT JOIN ' . $this->table_prefix . 'eventboard_participants p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL)
					AND e.start_at >= ' . (int) time() . '
				ORDER BY e.start_at ASC';
		}
		else
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
					(SELECT COUNT(p.id) FROM ' . $this->table_prefix . 'eventboard_participants p WHERE p.event_id = e.event_id) as num_participants
				FROM ' . $this->table_prefix . 'eventboard_events e
				LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
				WHERE e.visibility = 0
					AND e.start_at >= ' . (int) time() . '
				ORDER BY e.start_at ASC';
		}

		return $this->fetch_all($sql, $limit, $start);
	}

	public function count_upcoming_public_events($user_id = 0)
	{
		$user_id = (int) $user_id;

		if ($user_id > 1)
		{
			$sql = 'SELECT COUNT(e.event_id) as total
				FROM ' . $this->table_prefix . 'eventboard_events e
				LEFT JOIN ' . $this->table_prefix . 'eventboard_participants p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL)
					AND e.start_at >= ' . (int) time();
		}
		else
		{
			$sql = 'SELECT COUNT(e.event_id) as total
				FROM ' . $this->table_prefix . 'eventboard_events e
				WHERE e.visibility = 0
					AND e.start_at >= ' . (int) time();
		}

		return $this->fetch_count($sql);
	}

	public function get_occurring_public_events($user_id = 0)
	{
		$user_id = (int) $user_id;
		$now = (int) time();

		if ($user_id > 1)
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon
				FROM ' . $this->table_prefix . 'eventboard_events e
				LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
				LEFT JOIN ' . $this->table_prefix . 'eventboard_participants p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL)
					AND e.start_at <= ' . $now . '
					AND e.end_at >= ' . $now . '
				ORDER BY e.start_at ASC';
		}
		else
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon
				FROM ' . $this->table_prefix . 'eventboard_events e
				LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
				WHERE e.visibility = 0
					AND e.start_at <= ' . $now . '
					AND e.end_at >= ' . $now . '
				ORDER BY e.start_at ASC';
		}

		return $this->fetch_all($sql);
	}

	public function get_total_events_count()
	{
		$sql = 'SELECT COUNT(event_id) as total FROM ' . $this->table_prefix . 'eventboard_events WHERE visibility = 0';
		return $this->fetch_count($sql);
	}

	public function get_category_filters($active_only = true, $limit = null, $user_id = 0)
	{
		$user_id = (int) $user_id;

		if ($active_only)
		{
			if ($user_id > 1)
			{
				$sql = 'SELECT c.cat_id, c.cat_name, c.cat_color, c.cat_icon, COUNT(e.event_id) as event_count
					FROM ' . $this->table_prefix . 'eventboard_categories c
					LEFT JOIN ' . $this->table_prefix . 'eventboard_events e ON (c.cat_id = e.cat_id AND e.start_at >= ' . (int) time() . ' AND (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR EXISTS(
						SELECT 1 FROM ' . $this->table_prefix . 'eventboard_participants p
						WHERE p.event_id = e.event_id AND p.user_id = ' . (int) $user_id . '
					)))
					GROUP BY c.cat_id, c.cat_name, c.cat_color, c.cat_icon
					ORDER BY c.cat_name ASC';
			}
			else
			{
				$sql = 'SELECT c.cat_id, c.cat_name, c.cat_color, c.cat_icon, COUNT(e.event_id) as event_count
					FROM ' . $this->table_prefix . 'eventboard_categories c
					LEFT JOIN ' . $this->table_prefix . 'eventboard_events e ON (c.cat_id = e.cat_id AND e.start_at >= ' . (int) time() . ' AND e.visibility = 0)
					GROUP BY c.cat_id, c.cat_name, c.cat_color, c.cat_icon
					ORDER BY c.cat_name ASC';
			}
		}
		else
		{
			$sql = 'SELECT c.cat_id, c.cat_name, c.cat_color, c.cat_icon, COUNT(e.event_id) as event_count
				FROM ' . $this->table_prefix . 'eventboard_categories c
				LEFT JOIN ' . $this->table_prefix . 'eventboard_events e ON (c.cat_id = e.cat_id)
				GROUP BY c.cat_id, c.cat_name, c.cat_color, c.cat_icon
				ORDER BY c.cat_name ASC';
		}

		return $this->fetch_all($sql, $limit);
	}

	public function get_category_list($limit = null)
	{
		$sql = 'SELECT cat_id, cat_name, cat_icon, cat_color
            FROM ' . $this->table_prefix . 'eventboard_categories
            ORDER BY cat_id ASC';

		return $this->fetch_all($sql, $limit);
	}

	public function get_event_with_details($event_id)
	{
		$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
                u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height,
                gr.group_name
            FROM ' . $this->table_prefix . 'eventboard_events e
            LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
            LEFT JOIN ' . $this->table_prefix . 'users u ON (e.user_id = u.user_id)
            LEFT JOIN ' . $this->table_prefix . 'groups gr ON (u.group_id = gr.group_id)
            WHERE e.event_id = ' . (int) $event_id;

		return $this->fetch_row($sql);
	}

	public function get_event_basic($event_id)
	{
		$sql = 'SELECT *
            FROM ' . $this->table_prefix . 'eventboard_events
            WHERE event_id = ' . (int) $event_id;

		return $this->fetch_row($sql);
	}

	public function get_event_for_join($event_id)
	{
		$sql = 'SELECT event_id, user_id, title, max_participants, start_at, end_at, visibility, access_token
            FROM ' . $this->table_prefix . 'eventboard_events
            WHERE event_id = ' . (int) $event_id;

		return $this->fetch_row($sql);
	}

	public function get_event_for_redirect($event_id)
	{
		$sql = 'SELECT event_id, user_id, visibility, access_token
            FROM ' . $this->table_prefix . 'eventboard_events
            WHERE event_id = ' . (int) $event_id;

		return $this->fetch_row($sql);
	}

	public function get_event_for_comment($event_id)
	{
		$sql = 'SELECT event_id, user_id, title, start_at, end_at, visibility, access_token
            FROM ' . $this->table_prefix . 'eventboard_events
            WHERE event_id = ' . (int) $event_id;

		return $this->fetch_row($sql);
	}

	public function get_event_participants($event_id, $limit = 200)
	{
		$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height
            FROM ' . $this->table_prefix . 'eventboard_participants p
            JOIN ' . $this->table_prefix . 'users u ON (p.user_id = u.user_id)
            WHERE p.event_id = ' . (int) $event_id . '
            ORDER BY p.joined_at DESC';

		return $this->fetch_all($sql, $limit);
	}

	public function get_category($category_id)
	{
		$sql = 'SELECT *
            FROM ' . $this->table_prefix . 'eventboard_categories
            WHERE cat_id = ' . (int) $category_id;

		return $this->fetch_row($sql);
	}

	public function count_public_category_events($category_id, $user_id = 0)
	{
		$user_id = (int) $user_id;

		if ($user_id > 1)
		{
			$sql = 'SELECT COUNT(e.event_id) as total
				FROM ' . $this->table_prefix . 'eventboard_events e
				LEFT JOIN ' . $this->table_prefix . 'eventboard_participants p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE e.cat_id = ' . (int) $category_id . '
					AND (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL)
					AND e.start_at >= ' . (int) time();
		}
		else
		{
			$sql = 'SELECT COUNT(e.event_id) as total
				FROM ' . $this->table_prefix . 'eventboard_events e
				WHERE e.cat_id = ' . (int) $category_id . '
					AND e.visibility = 0
					AND e.start_at >= ' . (int) time();
		}

		return $this->fetch_count($sql);
	}

	public function get_public_category_events($category_id, $limit, $start = 0, $user_id = 0)
	{
		$user_id = (int) $user_id;

		if ($user_id > 1)
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
					(SELECT COUNT(p.id) FROM ' . $this->table_prefix . 'eventboard_participants p WHERE p.event_id = e.event_id) as num_participants
				FROM ' . $this->table_prefix . 'eventboard_events e
				LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
				LEFT JOIN ' . $this->table_prefix . 'eventboard_participants p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE e.cat_id = ' . (int) $category_id . '
					AND (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL)
					AND e.start_at >= ' . (int) time() . '
				ORDER BY e.start_at ASC';
		}
		else
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
					(SELECT COUNT(p.id) FROM ' . $this->table_prefix . 'eventboard_participants p WHERE p.event_id = e.event_id) as num_participants
				FROM ' . $this->table_prefix . 'eventboard_events e
				LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
				WHERE e.cat_id = ' . (int) $category_id . '
					AND e.visibility = 0
					AND e.start_at >= ' . (int) time() . '
				ORDER BY e.start_at ASC';
		}

		return $this->fetch_all($sql, $limit, $start);
	}

	public function get_owned_event_stats($user_id)
	{
		return [
			'active' => $this->fetch_count(
				'SELECT COUNT(event_id) as total
				FROM ' . $this->table_prefix . 'eventboard_events
				WHERE user_id = ' . (int) $user_id . '
					AND start_at >= ' . (int) time()
			),
			'signups' => $this->fetch_count(
				'SELECT COUNT(p.id) as total
				FROM ' . $this->table_prefix . 'eventboard_participants p
				JOIN ' . $this->table_prefix . 'eventboard_events e ON (p.event_id = e.event_id)
				WHERE e.user_id = ' . (int) $user_id . '
					AND e.start_at >= ' . (int) time()
			),
			'created' => $this->fetch_count(
				'SELECT COUNT(event_id) as total
				FROM ' . $this->table_prefix . 'eventboard_events
				WHERE user_id = ' . (int) $user_id
			),
		];
	}

	public function count_owned_events($user_id, $completed_view)
	{
		$sql = 'SELECT COUNT(e.event_id) as total
			FROM ' . $this->table_prefix . 'eventboard_events e
			WHERE e.user_id = ' . (int) $user_id . '
				AND e.start_at ' . ($completed_view ? '< ' : '>= ') . (int) time();

		return $this->fetch_count($sql);
	}

	public function get_owned_events($user_id, $completed_view, $limit, $start = 0)
	{
		$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
				(SELECT COUNT(p.id) FROM ' . $this->table_prefix . 'eventboard_participants p WHERE p.event_id = e.event_id) as num_participants
			FROM ' . $this->table_prefix . 'eventboard_events e
			LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
			WHERE e.user_id = ' . (int) $user_id . '
				AND e.start_at ' . ($completed_view ? '< ' : '>= ') . (int) time() . '
			ORDER BY e.start_at ' . ($completed_view ? 'DESC' : 'ASC');

		return $this->fetch_all($sql, $limit, $start);
	}

	public function count_user_rsvps($user_id)
	{
		return $this->fetch_count(
			'SELECT COUNT(p.id) as total
			FROM ' . $this->table_prefix . 'eventboard_participants p
			JOIN ' . $this->table_prefix . 'eventboard_events e ON (p.event_id = e.event_id)
			WHERE p.user_id = ' . (int) $user_id . '
				AND e.start_at >= ' . (int) time()
		);
	}

	public function count_rsvp_events($user_id)
	{
		return $this->fetch_count(
			'SELECT COUNT(e.event_id) as total
			FROM ' . $this->table_prefix . 'eventboard_events e
			JOIN ' . $this->table_prefix . 'eventboard_participants p ON (e.event_id = p.event_id)
			WHERE p.user_id = ' . (int) $user_id . '
				AND e.start_at >= ' . (int) time()
		);
	}

	public function get_rsvp_events($user_id, $limit, $start = 0)
	{
		$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
			(SELECT COUNT(participants.id) FROM ' . $this->table_prefix . 'eventboard_participants participants WHERE participants.event_id = e.event_id) as num_participants
			FROM ' . $this->table_prefix . 'eventboard_events e
			JOIN ' . $this->table_prefix . 'eventboard_participants p ON (e.event_id = p.event_id)
			LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
			WHERE p.user_id = ' . (int) $user_id . '
				AND e.start_at >= ' . (int) time() . '
			ORDER BY e.start_at ASC';

		return $this->fetch_all($sql, $limit, $start);
	}

	public function get_public_feed_events($limit)
	{
		$sql = 'SELECT e.*, c.cat_name, u.username
			FROM ' . $this->table_prefix . 'eventboard_events e
			LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
			LEFT JOIN ' . $this->table_prefix . 'users u ON (e.user_id = u.user_id)
			WHERE e.start_at >= ' . (int) time() . '
				AND e.visibility = 0
			ORDER BY e.start_at ASC';

		return $this->fetch_all($sql, $limit);
	}

	public function get_public_ical_events()
	{
		$sql = 'SELECT e.*, c.cat_name
			FROM ' . $this->table_prefix . 'eventboard_events e
			LEFT JOIN ' . $this->table_prefix . 'eventboard_categories c ON (e.cat_id = c.cat_id)
			WHERE e.visibility = 0
				AND e.start_at >= ' . (int) time() . '
			ORDER BY e.start_at ASC';

		return $this->fetch_all($sql);
	}

	protected function fetch_count($sql)
	{
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $count;
	}

	protected function fetch_row($sql)
	{
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	protected function fetch_all($sql, $limit = 0, $start = 0)
	{
		$rows = [];
		$result = $limit ? $this->db->sql_query_limit($sql, $limit, $start) : $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}

		$this->db->sql_freeresult($result);

		return $rows;
	}
}
