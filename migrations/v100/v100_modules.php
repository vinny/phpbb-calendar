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

class v100_modules extends \phpbb\db\migration\migration
{
	/**
	 * Check if migration is effectively installed.
	 *
	 * @return bool
	 */
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_langname = 'ACP_EVENTBOARD'
				AND module_class = 'acp'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return (bool) $row;
	}

	/**
	 * Define migration dependencies.
	 *
	 * @return array
	 */
	public static function depends_on()
	{
		return ['\vinny\calendar\migrations\v100\v100_configs'];
	}

	/**
	 * Updates database data.
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_EVENTBOARD',
			]],
			['module.add', [
				'acp',
				'ACP_EVENTBOARD',
				[
					'module_basename' => '\vinny\calendar\acp\main_module',
					'modes'           => ['settings', 'categories', 'manage_events'],
				],
			]],
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
			['module.remove', [
				'acp',
				'ACP_EVENTBOARD',
				[
					'module_basename' => '\vinny\calendar\acp\main_module',
					'modes'           => ['settings', 'categories', 'manage_events'],
				],
			]],
			['module.remove', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_EVENTBOARD',
			]],
		];
	}
}
