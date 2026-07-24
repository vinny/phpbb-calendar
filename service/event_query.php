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

		if (!defined('EVENTBOARD_EVENTS_TABLE'))
		{
			define('EVENTBOARD_EVENTS_TABLE', $table_prefix . 'eventboard_events');
			define('EVENTBOARD_CATEGORIES_TABLE', $table_prefix . 'eventboard_categories');
			define('EVENTBOARD_PARTICIPANTS_TABLE', $table_prefix . 'eventboard_participants');
			define('EVENTBOARD_COMMENTS_TABLE', $table_prefix . 'eventboard_comments');
		}
	}

	public function get_public_calendar_events($user_id = 0, $token = '')
	{
		$user_id = (int) $user_id;
		$token = preg_replace('/[^a-f0-9]/i', '', (string) $token);

		if ($user_id > 1)
		{
			$sql = 'SELECT e.event_id, e.title, e.start_at, e.end_at, e.visibility, e.access_token, c.cat_color, c.cat_icon
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
				LEFT JOIN ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')';
		}
		else
		{
			$sql = 'SELECT e.event_id, e.title, e.start_at, e.end_at, e.visibility, e.access_token, c.cat_color, c.cat_icon
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
				WHERE (e.visibility = 0' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')';
		}

		return $this->fetch_all($sql);
	}

	public function get_upcoming_public_events($limit, $start = 0, $user_id = 0, $token = '')
	{
		$user_id = (int) $user_id;
		$token = preg_replace('/[^a-f0-9]/i', '', (string) $token);

		if ($user_id > 1)
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
					(SELECT COUNT(p.id) FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p WHERE p.event_id = e.event_id) as num_participants
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
				LEFT JOIN ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')
					AND e.start_at >= ' . (int) time() . '
				ORDER BY e.start_at ASC';
		}
		else
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
					(SELECT COUNT(p.id) FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p WHERE p.event_id = e.event_id) as num_participants
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
				WHERE (e.visibility = 0' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')
					AND e.start_at >= ' . (int) time() . '
				ORDER BY e.start_at ASC';
		}

		return $this->fetch_all($sql, $limit, $start);
	}

	public function count_upcoming_public_events($user_id = 0, $token = '')
	{
		$user_id = (int) $user_id;
		$token = preg_replace('/[^a-f0-9]/i', '', (string) $token);

		if ($user_id > 1)
		{
			$sql = 'SELECT COUNT(e.event_id) as total
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')
					AND e.start_at >= ' . (int) time();
		}
		else
		{
			$sql = 'SELECT COUNT(e.event_id) as total
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				WHERE (e.visibility = 0' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')
					AND e.start_at >= ' . (int) time();
		}

		return $this->fetch_count($sql);
	}

	public function get_occurring_public_events($user_id = 0, $token = '')
	{
		$user_id = (int) $user_id;
		$token = preg_replace('/[^a-f0-9]/i', '', (string) $token);
		$now = (int) time();

		if ($user_id > 1)
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon, u.username, u.user_colour
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
				LEFT JOIN ' . USERS_TABLE . ' u ON (e.user_id = u.user_id)
				LEFT JOIN ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')
					AND e.start_at <= ' . $now . '
					AND e.end_at >= ' . $now . '
				ORDER BY e.start_at ASC';
		}
		else
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon, u.username, u.user_colour
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
				LEFT JOIN ' . USERS_TABLE . ' u ON (e.user_id = u.user_id)
				WHERE (e.visibility = 0' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')
					AND e.start_at <= ' . $now . '
					AND e.end_at >= ' . $now . '
				ORDER BY e.start_at ASC';
		}

		return $this->fetch_all($sql);
	}

	public function get_total_events_count()
	{
		$sql = 'SELECT COUNT(event_id) as total FROM ' . EVENTBOARD_EVENTS_TABLE . ' WHERE visibility = 0';
		return $this->fetch_count($sql, 300);
	}

	public function get_category_filters($active_only = true, $limit = null, $user_id = 0)
	{
		$user_id = (int) $user_id;

		if ($active_only)
		{
			if ($user_id > 1)
			{
				$sql = 'SELECT c.cat_id, c.cat_name, c.cat_color, c.cat_icon, COUNT(e.event_id) as event_count
					FROM ' . EVENTBOARD_CATEGORIES_TABLE . ' c
					LEFT JOIN ' . EVENTBOARD_EVENTS_TABLE . ' e ON (c.cat_id = e.cat_id AND e.start_at >= ' . (int) time() . ' AND (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR EXISTS(
						SELECT 1 FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p
						WHERE p.event_id = e.event_id AND p.user_id = ' . (int) $user_id . '
					)))
					GROUP BY c.cat_id, c.cat_name, c.cat_color, c.cat_icon
					ORDER BY c.cat_name ASC';
			}
			else
			{
				$sql = 'SELECT c.cat_id, c.cat_name, c.cat_color, c.cat_icon, COUNT(e.event_id) as event_count
					FROM ' . EVENTBOARD_CATEGORIES_TABLE . ' c
					LEFT JOIN ' . EVENTBOARD_EVENTS_TABLE . ' e ON (c.cat_id = e.cat_id AND e.start_at >= ' . (int) time() . ' AND e.visibility = 0)
					GROUP BY c.cat_id, c.cat_name, c.cat_color, c.cat_icon
					ORDER BY c.cat_name ASC';
			}
		}
		else
		{
			$sql = 'SELECT c.cat_id, c.cat_name, c.cat_color, c.cat_icon, COUNT(e.event_id) as event_count
				FROM ' . EVENTBOARD_CATEGORIES_TABLE . ' c
				LEFT JOIN ' . EVENTBOARD_EVENTS_TABLE . ' e ON (c.cat_id = e.cat_id)
				GROUP BY c.cat_id, c.cat_name, c.cat_color, c.cat_icon
				ORDER BY c.cat_name ASC';
		}

		return $this->fetch_all($sql, $limit);
	}

	public function get_category_list($limit = null)
	{
		$sql = 'SELECT cat_id, cat_name, cat_icon, cat_color
            FROM ' . EVENTBOARD_CATEGORIES_TABLE . '
            ORDER BY cat_id ASC';

		return $this->fetch_all($sql, $limit, 0, 3600);
	}

	public function get_event_with_details($event_id)
	{
		$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
                u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height,
                gr.group_name
            FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
            LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
            LEFT JOIN ' . USERS_TABLE . ' u ON (e.user_id = u.user_id)
            LEFT JOIN ' . GROUPS_TABLE . ' gr ON (u.group_id = gr.group_id)
            WHERE e.event_id = ' . (int) $event_id;

		return $this->fetch_row($sql);
	}

	public function get_event_basic($event_id)
	{
		$sql = 'SELECT *
            FROM ' . EVENTBOARD_EVENTS_TABLE . '
            WHERE event_id = ' . (int) $event_id;

		return $this->fetch_row($sql);
	}

	public function get_event_for_join($event_id)
	{
		$sql = 'SELECT event_id, user_id, title, max_participants, start_at, end_at, visibility, access_token
            FROM ' . EVENTBOARD_EVENTS_TABLE . '
            WHERE event_id = ' . (int) $event_id;

		return $this->fetch_row($sql);
	}

	public function get_event_for_redirect($event_id)
	{
		$sql = 'SELECT event_id, user_id, visibility, access_token
            FROM ' . EVENTBOARD_EVENTS_TABLE . '
            WHERE event_id = ' . (int) $event_id;

		return $this->fetch_row($sql);
	}

	public function get_event_for_comment($event_id)
	{
		$sql = 'SELECT event_id, user_id, title, start_at, end_at, visibility, access_token
            FROM ' . EVENTBOARD_EVENTS_TABLE . '
            WHERE event_id = ' . (int) $event_id;

		return $this->fetch_row($sql);
	}

	public function get_event_participants($event_id, $limit = 200)
	{
		$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height
            FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p
            JOIN ' . USERS_TABLE . ' u ON (p.user_id = u.user_id)
            WHERE p.event_id = ' . (int) $event_id . '
            ORDER BY p.joined_at DESC';

		return $this->fetch_all($sql, $limit);
	}

	public function get_category($category_id)
	{
		$sql = 'SELECT *
            FROM ' . EVENTBOARD_CATEGORIES_TABLE . '
            WHERE cat_id = ' . (int) $category_id;

		return $this->fetch_row($sql);
	}

	public function count_public_category_events($category_id, $user_id = 0, $token = '')
	{
		$user_id = (int) $user_id;
		$token = preg_replace('/[^a-f0-9]/i', '', (string) $token);

		if ($user_id > 1)
		{
			$sql = 'SELECT COUNT(e.event_id) as total
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE e.cat_id = ' . (int) $category_id . '
					AND (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')
					AND e.start_at >= ' . (int) time();
		}
		else
		{
			$sql = 'SELECT COUNT(e.event_id) as total
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				WHERE e.cat_id = ' . (int) $category_id . '
					AND (e.visibility = 0' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')
					AND e.start_at >= ' . (int) time();
		}

		return $this->fetch_count($sql);
	}

	public function get_public_category_events($category_id, $limit, $start = 0, $user_id = 0, $token = '')
	{
		$user_id = (int) $user_id;
		$token = preg_replace('/[^a-f0-9]/i', '', (string) $token);

		if ($user_id > 1)
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
					(SELECT COUNT(p.id) FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p WHERE p.event_id = e.event_id) as num_participants
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
				LEFT JOIN ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p ON (e.event_id = p.event_id AND p.user_id = ' . (int) $user_id . ')
				WHERE e.cat_id = ' . (int) $category_id . '
					AND (e.visibility = 0 OR e.user_id = ' . (int) $user_id . ' OR p.user_id IS NOT NULL' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')
					AND e.start_at >= ' . (int) time() . '
				ORDER BY e.start_at ASC';
		}
		else
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
					(SELECT COUNT(p.id) FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p WHERE p.event_id = e.event_id) as num_participants
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
				WHERE e.cat_id = ' . (int) $category_id . '
					AND (e.visibility = 0' . ($token !== '' ? " OR e.access_token = '" . $this->db->sql_escape($token) . "'" : '') . ')
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
				FROM ' . EVENTBOARD_EVENTS_TABLE . '
				WHERE user_id = ' . (int) $user_id . '
					AND start_at >= ' . (int) time()
			),
			'signups' => $this->fetch_count(
				'SELECT COUNT(p.id) as total
				FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p
				JOIN ' . EVENTBOARD_EVENTS_TABLE . ' e ON (p.event_id = e.event_id)
				WHERE e.user_id = ' . (int) $user_id . '
					AND e.start_at >= ' . (int) time()
			),
			'created' => $this->fetch_count(
				'SELECT COUNT(event_id) as total
				FROM ' . EVENTBOARD_EVENTS_TABLE . '
				WHERE user_id = ' . (int) $user_id
			),
		];
	}

	public function count_owned_events($user_id, $completed_view)
	{
		if ($completed_view)
		{
			$sql = 'SELECT COUNT(e.event_id) as total
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				WHERE e.user_id = ' . (int) $user_id . '
					AND e.start_at < ' . (int) time();
		}
		else
		{
			$sql = 'SELECT COUNT(e.event_id) as total
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				WHERE e.user_id = ' . (int) $user_id . '
					AND e.start_at >= ' . (int) time();
		}

		return $this->fetch_count($sql);
	}

	public function get_owned_events($user_id, $completed_view, $limit, $start = 0)
	{
		if ($completed_view)
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
					(SELECT COUNT(p.id) FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p WHERE p.event_id = e.event_id) as num_participants
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
				WHERE e.user_id = ' . (int) $user_id . '
					AND e.start_at < ' . (int) time() . '
				ORDER BY e.start_at DESC';
		}
		else
		{
			$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
					(SELECT COUNT(p.id) FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p WHERE p.event_id = e.event_id) as num_participants
				FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
				LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
				WHERE e.user_id = ' . (int) $user_id . '
					AND e.start_at >= ' . (int) time() . '
				ORDER BY e.start_at ASC';
		}

		return $this->fetch_all($sql, $limit, $start);
	}

	public function count_user_rsvps($user_id)
	{
		return $this->fetch_count(
			'SELECT COUNT(p.id) as total
			FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p
			JOIN ' . EVENTBOARD_EVENTS_TABLE . ' e ON (p.event_id = e.event_id)
			WHERE p.user_id = ' . (int) $user_id . '
				AND e.start_at >= ' . (int) time()
		);
	}

	public function count_rsvp_events($user_id)
	{
		return $this->fetch_count(
			'SELECT COUNT(e.event_id) as total
			FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
			JOIN ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p ON (e.event_id = p.event_id)
			WHERE p.user_id = ' . (int) $user_id . '
				AND e.start_at >= ' . (int) time()
		);
	}

	public function get_rsvp_events($user_id, $limit, $start = 0)
	{
		$sql = 'SELECT e.*, c.cat_name, c.cat_color, c.cat_icon,
			(SELECT COUNT(participants.id) FROM ' . EVENTBOARD_PARTICIPANTS_TABLE . ' participants WHERE participants.event_id = e.event_id) as num_participants
			FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
			JOIN ' . EVENTBOARD_PARTICIPANTS_TABLE . ' p ON (e.event_id = p.event_id)
			LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
			WHERE p.user_id = ' . (int) $user_id . '
				AND e.start_at >= ' . (int) time() . '
			ORDER BY e.start_at ASC';

		return $this->fetch_all($sql, $limit, $start);
	}

	public function get_public_feed_events($limit)
	{
		$sql = 'SELECT e.*, c.cat_name, u.username
			FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
			LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
			LEFT JOIN ' . USERS_TABLE . ' u ON (e.user_id = u.user_id)
			WHERE e.start_at >= ' . (int) time() . '
				AND e.visibility = 0
			ORDER BY e.start_at ASC';

		return $this->fetch_all($sql, $limit);
	}

	public function get_public_ical_events()
	{
		$sql = 'SELECT e.*, c.cat_name
			FROM ' . EVENTBOARD_EVENTS_TABLE . ' e
			LEFT JOIN ' . EVENTBOARD_CATEGORIES_TABLE . ' c ON (e.cat_id = c.cat_id)
			WHERE e.visibility = 0
				AND e.start_at >= ' . (int) time() . '
			ORDER BY e.start_at ASC';

		return $this->fetch_all($sql);
	}

	protected function fetch_count($sql, $cache_ttl = 0)
	{
		$result = $this->db->sql_query($sql, $cache_ttl);
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

	protected function fetch_all($sql, $limit = 0, $start = 0, $cache_ttl = 0)
	{
		$rows = [];
		$result = $limit ? $this->db->sql_query_limit($sql, $limit, $start, $cache_ttl) : $this->db->sql_query($sql, $cache_ttl);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}

		$this->db->sql_freeresult($result);

		return $rows;
	}
}
