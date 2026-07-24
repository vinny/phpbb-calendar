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

class calendar_link
{
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \vinny\calendar\service\event_access */
	protected $event_access;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\request\request $request, \vinny\calendar\service\event_access $event_access)
	{
		$this->helper = $helper;
		$this->request = $request;
		$this->event_access = $event_access;
	}

	public function route($route, array $event, array $params = [])
	{
		return $this->helper->route(
			$route,
			$this->event_access->build_route_params($event, $params, $this->current_access_token())
		);
	}

	public function private_route($route, $event_id, $visibility, $access_token, array $params = [])
	{
		return $this->helper->route(
			$route,
			$this->event_access->build_route_params([
				'event_id' => (int) $event_id,
				'visibility' => (int) $visibility,
				'access_token' => $access_token,
			], $params ?: ['id' => (int) $event_id], $this->current_access_token())
		);
	}

	public function current_access_token()
	{
		return $this->event_access->normalize_token($this->request->variable('t', '', true));
	}

	public function absolute_route($board_url, $route, array $event, array $params = [])
	{
		$route_url = $this->route($route, $event, $params);
		return $this->absolute_url($board_url, $route_url);
	}

	public function absolute_url($board_url, $route_url)
	{
		if (preg_match('#^https?://#i', $route_url))
		{
			return $route_url;
		}

		$parts = parse_url($board_url);
		$root_url = $parts['scheme'] . '://' . $parts['host'];
		if (!empty($parts['port']))
		{
			$root_url .= ':' . $parts['port'];
		}

		$board_path = isset($parts['path']) ? rtrim($parts['path'], '/') : '';
		if ($board_path !== '' && strpos($route_url, $board_path . '/') === 0)
		{
			return $root_url . $route_url;
		}

		return rtrim($board_url, '/') . $route_url;
	}
}
