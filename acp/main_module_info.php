<?php

/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\acp;

class main_module_info
{
	public function module()
	{
		return [
			'filename' => '\vinny\calendar\acp\main_module',
			'title' => 'ACP_EVENTBOARD',
			'modes' => [
				'settings' => [
					'title' => 'ACP_EVENTBOARD_SETTINGS',
					'auth' => 'ext_vinny/calendar && acl_a_board',
					'cat' => ['ACP_EVENTBOARD']
				],
				'categories' => [
					'title' => 'ACP_EVENTBOARD_CATEGORIES',
					'auth' => 'ext_vinny/calendar && acl_a_board',
					'cat' => ['ACP_EVENTBOARD']
				],
				'manage_events' => [
					'title' => 'ACP_EVENTBOARD_MANAGE',
					'auth' => 'ext_vinny/calendar && acl_a_board',
					'cat' => ['ACP_EVENTBOARD']
				],
			],
		];
	}
}
