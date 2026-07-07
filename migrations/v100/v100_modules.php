<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GPL-2.0-only
 *
 */

namespace vinny\calendar\migrations\v100;

class v100_modules extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\vinny\calendar\migrations\v100\v100_configs'];
	}

	public function update_data()
	{
		return [
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_EVENTBOARD'
			]],
			['module.add', [
				'acp',
				'ACP_EVENTBOARD',
				[
					'module_basename' => '\vinny\calendar\acp\main_module',
					'modes'           => ['settings', 'categories', 'manage_events'],
				]
			]],
		];
	}
}
