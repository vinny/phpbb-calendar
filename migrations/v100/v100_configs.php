<?php

/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\migrations\v100;

class v100_configs extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\vinny\calendar\migrations\v100\v100_schema'];
	}

	public function update_data()
	{
		return [
			['config.add', ['vinny_calendar_enable', 1]],
			['config.add', ['vinny_calendar_allow_comments', 1]],
			['config.add', ['vinny_calendar_enable_feed', 1]],
			['config.add', ['vinny_calendar_reminder_minutes', 0]],
			['config.add', ['vinny_calendar_reminder_last_run', 0]],
			['config.add', ['vinny_calendar_geoapify_key', '']],

			['config.add', ['vinny_calendar_fp_date_format', 'd/m/Y H:i']],

			['config.add', ['vinny_calendar_map_width', 1024]],
			['config.add', ['vinny_calendar_map_height', 768]],
			['config.add', ['vinny_calendar_map_zoom', 17]],
		];
	}
}
