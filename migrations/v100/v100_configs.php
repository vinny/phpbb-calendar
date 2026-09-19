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
	/**
	 * Check if migration is effectively installed.
	 *
	 * @return bool
	 */
	public function effectively_installed()
	{
		return isset($this->config['vinny_calendar_enable']);
	}

	/**
	 * Define migration dependencies.
	 *
	 * @return array
	 */
	public static function depends_on()
	{
		return ['\vinny\calendar\migrations\v100\v100_schema'];
	}

	/**
	 * Updates database data.
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			['config.add', ['vinny_calendar_enable', 1]],
			['config.add', ['vinny_calendar_allow_comments', 1]],
			['config.add', ['vinny_calendar_enable_feed', 1]],
			['config.add', ['vinny_calendar_reminder_minutes', 0]],
			['config.add', ['vinny_calendar_reminder_last_run', 0, true]],
			['config.add', ['vinny_calendar_geoapify_key', '']],

			['config.add', ['vinny_calendar_map_width', 1024]],
			['config.add', ['vinny_calendar_map_height', 768]],
			['config.add', ['vinny_calendar_map_zoom', 17]],

			['config.add', ['vinny_calendar_display_occurring', 1]],
			['config.add', ['vinny_calendar_display_upcoming', 1]],
			['config.add', ['vinny_calendar_display_stats', 1]],
		];
	}

	/**
	 * Reverts database data.
	 *
	 * @return array
	 */
	public function revert_data()
	{
		return [
			['config.remove', ['vinny_calendar_enable']],
			['config.remove', ['vinny_calendar_allow_comments']],
			['config.remove', ['vinny_calendar_enable_feed']],
			['config.remove', ['vinny_calendar_reminder_minutes']],
			['config.remove', ['vinny_calendar_reminder_last_run']],
			['config.remove', ['vinny_calendar_geoapify_key']],
			['config.remove', ['vinny_calendar_map_width']],
			['config.remove', ['vinny_calendar_map_height']],
			['config.remove', ['vinny_calendar_map_zoom']],
			['config.remove', ['vinny_calendar_display_occurring']],
			['config.remove', ['vinny_calendar_display_upcoming']],
			['config.remove', ['vinny_calendar_display_stats']],
		];
	}
}
