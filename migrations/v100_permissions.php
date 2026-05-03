<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license All Rights Reserved
 *
 */

namespace vinny\calendar\migrations;

class v100_permissions extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\vinny\calendar\migrations\v100_configs'];
	}

	public function update_data()
	{
		return [
			['permission.add', ['u_eventboard_view', true]],
			['permission.add', ['u_eventboard_create', true]],
			['permission.add', ['u_eventboard_delete', true]],

			// ADMINISTRATORS
			['permission.permission_set', ['ADMINISTRATORS', 'u_eventboard_view', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_eventboard_create', 'group']],
			['permission.permission_set', ['ADMINISTRATORS', 'u_eventboard_delete', 'group']],

			// GLOBAL_MODERATORS
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_eventboard_view', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_eventboard_create', 'group']],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_eventboard_delete', 'group']],

			// REGISTERED
			['permission.permission_set', ['REGISTERED', 'u_eventboard_view', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_eventboard_create', 'group']],
			['permission.permission_set', ['REGISTERED', 'u_eventboard_delete', 'group']],

			// GUESTS
			['permission.permission_set', ['GUESTS', 'u_eventboard_view', 'group']],

			// BOTS
			['permission.permission_set', ['BOTS', 'u_eventboard_view', 'group']],
		];
	}
}
