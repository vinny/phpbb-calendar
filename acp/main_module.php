<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GPL-2.0-only
 *
 */

namespace vinny\calendar\acp;

class main_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user, $phpbb_container;

		$user->add_lang_ext('vinny/calendar', 'common');
		$user->add_lang_ext('vinny/calendar', 'info_acp_calendar');

		$this->tpl_name = 'acp_eventboard_' . $mode;
		$this->page_title = $user->lang('ACP_EVENTBOARD') . ' - ' . $user->lang('ACP_EVENTBOARD_' . strtoupper($mode));

		switch ($mode)
		{
			case 'settings':
				$this->settings($config, $request, $template, $user, $phpbb_container);
			break;

			case 'categories':
				$this->categories($request, $template, $user, $phpbb_container);
			break;

			case 'manage_events':
				$this->manage_events($request, $template, $user, $phpbb_container);
			break;
		}
	}

	protected function settings($config, $request, $template, $user, $container)
	{
		$form_key = 'vinny_calendar_settings';
		add_form_key($form_key);

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error('FORM_INVALID');
			}

			$config->set('vinny_calendar_enable', $request->variable('vinny_calendar_enable', 0));
			$config->set('vinny_calendar_allow_comments', $request->variable('vinny_calendar_allow_comments', 0));
			$config->set('vinny_calendar_enable_feed', $request->variable('vinny_calendar_enable_feed', 0));
			$config->set('vinny_calendar_reminder_minutes', min(60, max(0, $request->variable('vinny_calendar_reminder_minutes', 0))));
			$config->set('vinny_calendar_geoapify_key', trim($request->variable('vinny_calendar_geoapify_key', '', true)));
			$config->set('vinny_calendar_map_width', min(2000, max(100, $request->variable('vinny_calendar_map_width', 1024))));
			$config->set('vinny_calendar_map_height', min(2000, max(100, $request->variable('vinny_calendar_map_height', 768))));
			$config->set('vinny_calendar_map_zoom', min(20, max(1, $request->variable('vinny_calendar_map_zoom', 17))));

			add_log('admin', 'LOG_EVENTBOARD_CONFIG_UPDATED');
			trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		$template->assign_vars([
			'U_ACTION' => $this->u_action,
			'VINNY_CALENDAR_ENABLE' => (int) $config['vinny_calendar_enable'],
			'VINNY_CALENDAR_ALLOW_COMMENTS' => (int) $config['vinny_calendar_allow_comments'],
			'VINNY_CALENDAR_ENABLE_FEED' => (int) $config['vinny_calendar_enable_feed'],
			'VINNY_CALENDAR_REMINDER_MINUTES' => (int) ($config['vinny_calendar_reminder_minutes'] ?? 0),
			'VINNY_CALENDAR_GEOAPIFY_KEY' => (string) ($config['vinny_calendar_geoapify_key'] ?? ''),
			'VINNY_CALENDAR_MAP_WIDTH' => (int) ($config['vinny_calendar_map_width'] ?? 1024),
			'VINNY_CALENDAR_MAP_HEIGHT' => (int) ($config['vinny_calendar_map_height'] ?? 768),
			'VINNY_CALENDAR_MAP_ZOOM' => (int) $config['vinny_calendar_map_zoom'],
		]);
	}

	protected function categories($request, $template, $user, $container)
	{
		global $table_prefix;

		$db = $container->get('dbal.conn');
		$table_categories = $table_prefix . 'eventboard_categories';
		$table_events = $table_prefix . 'eventboard_events';
		$action = $request->variable('action', '');
		$cat_id = $request->variable('c', 0);

		switch ($action)
		{
			case 'add':
			case 'edit':
				$category = [
					'cat_name' => '',
					'cat_desc' => '',
					'cat_color' => '3089a6',
					'cat_icon' => 'fa-calendar',
				];

				if ($cat_id)
				{
					$sql = 'SELECT *
						FROM ' . $table_categories . '
						WHERE cat_id = ' . (int) $cat_id;
					$result = $db->sql_query($sql);
					$category = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$category)
					{
						trigger_error('CATEGORY_NOT_FOUND');
					}
				}

				add_form_key('vinny_calendar_categories');

				if ($request->is_set_post('submit'))
				{
					if (!check_form_key('vinny_calendar_categories'))
					{
						trigger_error('FORM_INVALID');
					}

					$cat_name = trim($request->variable('cat_name', '', true));
					$cat_desc = trim($request->variable('cat_desc', '', true));
					$cat_color = ltrim(trim($request->variable('cat_color', '', true)), '#');
					$cat_icon = trim($request->variable('cat_icon', '', true));

					$error = '';
					if ($cat_name === '')
					{
						$error = $user->lang('CATEGORY_NAME_REQUIRED');
					}
					else if (!preg_match('/^[a-f0-9]{3,6}$/i', $cat_color))
					{
						$error = $user->lang('CATEGORY_COLOR_INVALID');
					}
					else if ($cat_icon === '' || !preg_match('/^[a-z0-9\-\s_]+$/i', $cat_icon))
					{
						$error = $user->lang('CATEGORY_ICON_INVALID');
					}

					if ($error === '')
					{
						$sql_ary = [
							'cat_name' => $cat_name,
							'cat_desc' => $cat_desc,
							'cat_color' => $cat_color,
							'cat_icon' => $cat_icon,
						];

						if ($action === 'add')
						{
							$sql = 'INSERT INTO ' . $table_categories . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
							add_log('admin', 'LOG_EVENTBOARD_CATEGORY_ADDED', $cat_name);
							trigger_error($user->lang('CATEGORY_ADDED') . adm_back_link($this->u_action));
						}

						$sql = 'UPDATE ' . $table_categories . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE cat_id = ' . (int) $cat_id;
						$db->sql_query($sql);
						add_log('admin', 'LOG_EVENTBOARD_CATEGORY_UPDATED', $cat_name);
						trigger_error($user->lang('CATEGORY_UPDATED') . adm_back_link($this->u_action));
					}

					$template->assign_vars([
						'S_ERROR' => true,
						'ERROR_MSG' => $error,
					]);

					$category['cat_name'] = $cat_name;
					$category['cat_desc'] = $cat_desc;
					$category['cat_color'] = $cat_color;
					$category['cat_icon'] = $cat_icon;
				}

				$template->assign_vars([
					'S_EDIT_CATEGORY' => true,
					'U_ACTION' => $this->u_action . '&amp;action=' . $action . '&amp;c=' . (int) $cat_id,
					'U_BACK' => $this->u_action,
					'CAT_NAME' => $category['cat_name'],
					'CAT_DESC' => $category['cat_desc'],
					'CAT_COLOR' => ltrim($category['cat_color'], '#'),
					'CAT_ICON' => $category['cat_icon'],
				]);
			return;

			case 'delete':
				$sql = 'SELECT c.cat_name, COUNT(e.event_id) AS event_count
					FROM ' . $table_categories . ' c
					LEFT JOIN ' . $table_events . ' e ON (e.cat_id = c.cat_id)
					WHERE c.cat_id = ' . (int) $cat_id . '
					GROUP BY c.cat_id, c.cat_name';
				$result = $db->sql_query($sql);
				$category = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$category)
				{
					trigger_error('CATEGORY_NOT_FOUND');
				}

				if ((int) $category['event_count'] > 0)
				{
					trigger_error('CATEGORY_HAS_EVENTS');
				}

				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . $table_categories . '
						WHERE cat_id = ' . (int) $cat_id;
					$db->sql_query($sql);
					add_log('admin', 'LOG_EVENTBOARD_CATEGORY_REMOVED', $category['cat_name']);

					if ($request->is_ajax())
					{
						$json_response = new \phpbb\json_response;
						$json_response->send([
							'SUCCESS' => true,
						]);
					}

					trigger_error($user->lang('CATEGORY_DELETED') . adm_back_link($this->u_action));
				}

				confirm_box(false, $user->lang('CONFIRM_DELETE_CATEGORY'), build_hidden_fields([
					'c' => $cat_id,
					'action' => 'delete',
				]));
			return;
		}

		$sql = 'SELECT c.*, COUNT(e.event_id) AS event_count
			FROM ' . $table_categories . ' c
			LEFT JOIN ' . $table_events . ' e ON (e.cat_id = c.cat_id)
			GROUP BY c.cat_id, c.cat_name, c.cat_desc, c.cat_color, c.cat_icon
			ORDER BY c.cat_name ASC';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('categories', [
				'NAME' => $row['cat_name'],
				'DESC' => $row['cat_desc'],
				'COLOR' => '#' . ltrim($row['cat_color'], '#'),
				'ICON' => $row['cat_icon'],
				'EVENT_COUNT' => (int) $row['event_count'],
				'S_CAN_DELETE' => ((int) $row['event_count'] === 0),
				'U_EDIT' => $this->u_action . '&amp;action=edit&amp;c=' . (int) $row['cat_id'],
				'U_DELETE' => $this->u_action . '&amp;action=delete&amp;c=' . (int) $row['cat_id'],
			]);
		}
		$db->sql_freeresult($result);

		$template->assign_vars([
			'U_ADD_CATEGORY' => $this->u_action . '&amp;action=add',
		]);
	}

	protected function manage_events($request, $template, $user, $container)
	{
		global $table_prefix;

		$db = $container->get('dbal.conn');
		$helper = $container->get('controller.helper');
		$pagination = $container->get('pagination');
		$action = $request->variable('action', '');
		$event_id = $request->variable('id', 0);
		$start = $request->variable('start', 0);
		$completed = $request->variable('completed', 0);
		$per_page = 15;

		$table_events = $table_prefix . 'eventboard_events';
		$table_users = $table_prefix . 'users';
		$table_categories = $table_prefix . 'eventboard_categories';
		$table_participants = $table_prefix . 'eventboard_participants';

		if ($action === 'delete' && $event_id)
		{
			if (confirm_box(true))
			{
				$db->sql_query('DELETE FROM ' . $table_events . ' WHERE event_id = ' . (int) $event_id);
				$db->sql_query('DELETE FROM ' . $table_participants . ' WHERE event_id = ' . (int) $event_id);
				$db->sql_query('DELETE FROM ' . $table_prefix . 'eventboard_comments WHERE event_id = ' . (int) $event_id);

				if ($request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send([
						'SUCCESS' => true,
					]);
				}

				trigger_error($user->lang('EVENT_DELETED') . adm_back_link($this->u_action));
			}

			confirm_box(false, $user->lang('CONFIRM_DELETE_EVENT'), build_hidden_fields([
				'id' => $event_id,
				'action' => 'delete',
				'completed' => $completed,
			]));
		}

		$where = $completed ? 'e.start_at < ' . time() : 'e.start_at >= ' . time();
		$total_events = $this->fetch_count($db, 'SELECT COUNT(event_id) AS total FROM ' . $table_events . ' e WHERE ' . $where);
		$total_active = $this->fetch_count($db, 'SELECT COUNT(event_id) AS total FROM ' . $table_events . ' WHERE start_at >= ' . time());
		$total_completed = $this->fetch_count($db, 'SELECT COUNT(event_id) AS total FROM ' . $table_events . ' WHERE start_at < ' . time());

		$sql = 'SELECT e.*, u.username, u.user_colour, c.cat_name,
				(SELECT COUNT(p.id) FROM ' . $table_participants . ' p WHERE p.event_id = e.event_id) AS participant_count
			FROM ' . $table_events . ' e
			LEFT JOIN ' . $table_users . ' u ON (u.user_id = e.user_id)
			LEFT JOIN ' . $table_categories . ' c ON (c.cat_id = e.cat_id)
			WHERE ' . $where . '
			ORDER BY e.start_at ' . ($completed ? 'DESC' : 'ASC');
		$result = $db->sql_query_limit($sql, $per_page, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$event_route = ['id' => (int) $row['event_id']];
			if ((int) $row['visibility'] === 1 && !empty($row['access_token']))
			{
				$event_route['t'] = $row['access_token'];
			}

			$template->assign_block_vars('events', [
				'TITLE' => $row['title'],
				'CATEGORY' => $row['cat_name'],
				'START_TIME' => $user->format_date($row['start_at']),
				'VISIBILITY' => ((int) $row['visibility'] === 0) ? $user->lang('YES') : $user->lang('NO'),
				'AUTHOR' => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'PARTICIPANT_COUNT' => (int) $row['participant_count'],
				'U_VIEW' => $helper->route('vinny_calendar_view', $event_route),
				'U_EDIT' => $helper->route('vinny_calendar_edit', $event_route),
				'U_DELETE' => $this->u_action . '&amp;action=delete&amp;id=' . (int) $row['event_id'] . '&amp;completed=' . (int) $completed,
			]);
		}
		$db->sql_freeresult($result);

		$base_url = $this->u_action . '&amp;completed=' . (int) $completed;
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_events, $per_page, $start);

		$template->assign_vars([
			'TOTAL_EVENTS' => $total_events,
			'TOTAL_ACTIVE_EVENTS' => $total_active,
			'TOTAL_COMPLETED_EVENTS' => $total_completed,
			'S_COMPLETED_VIEW' => (bool) $completed,
			'U_ACTIVE_EVENTS' => $this->u_action . '&amp;completed=0',
			'U_COMPLETED_EVENTS' => $this->u_action . '&amp;completed=1',
		]);
	}

	

	protected function fetch_count($db, $sql)
	{
		$result = $db->sql_query($sql);
		$total = (int) $db->sql_fetchfield('total');
		$db->sql_freeresult($result);

		return $total;
	}
}
