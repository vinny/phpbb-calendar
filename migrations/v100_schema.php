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

class v100_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'eventboard_events');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'eventboard_categories' => [
					'COLUMNS' => [
						'cat_id'    => ['UINT', null, 'auto_increment'],
						'cat_name'  => ['VCHAR:255', ''],
						'cat_color' => ['VCHAR:50', ''],
						'cat_icon'  => ['VCHAR:50', ''],
						'cat_desc'  => ['MTEXT_UNI', ''],
					],
					'PRIMARY_KEY' => 'cat_id',
				],
				$this->table_prefix . 'eventboard_events' => [
					'COLUMNS' => [
						'event_id'         => ['UINT', null, 'auto_increment'],
						'user_id'          => ['UINT', 0],
						'cat_id'           => ['UINT', 0],
						'title'            => ['VCHAR:255', ''],
						'description'      => ['MTEXT_UNI', ''],
						'desc_uid'         => ['VCHAR:8', ''],
						'desc_bitfield'    => ['VCHAR:255', ''],
						'desc_options'     => ['UINT:11', 7],
						'start_at'         => ['UINT:11', 0],
						'end_at'           => ['UINT:11', 0],
						'location'         => ['VCHAR:255', ''],
						'max_participants' => ['UINT', 0],
						'visibility'       => ['UINT', 0],
						'access_token'     => ['VCHAR:64', ''],
						'created_at'       => ['UINT:11', 0],
						'reminder_sent_at' => ['UINT:11', 0],
						'lat'              => ['VCHAR:50', '0'],
						'lng'              => ['VCHAR:50', '0'],
						'map_image'        => ['VCHAR:255', ''],
					],
					'PRIMARY_KEY' => 'event_id',
					'KEYS' => [
						'user_id'  => ['INDEX', 'user_id'],
						'start_at' => ['INDEX', 'start_at'],
						'cat_id'   => ['INDEX', 'cat_id'],
						'access_token' => ['INDEX', 'access_token'],
					],
				],
				$this->table_prefix . 'eventboard_participants' => [
					'COLUMNS' => [
						'id'        => ['UINT', null, 'auto_increment'],
						'event_id'  => ['UINT', 0],
						'user_id'   => ['UINT', 0],
						'joined_at' => ['UINT:11', 0],
					],
					'PRIMARY_KEY' => 'id',
					'KEYS' => [
						'event_user' => ['UNIQUE', ['event_id', 'user_id']],
					],
				],
				$this->table_prefix . 'eventboard_comments' => [
					'COLUMNS' => [
						'comment_id' => ['UINT', null, 'auto_increment'],
						'event_id'   => ['UINT', 0],
						'user_id'    => ['UINT', 0],
						'message'    => ['MTEXT_UNI', ''],
						'uid'        => ['VCHAR:8', ''],
						'bitfield'   => ['VCHAR:255', ''],
						'options'    => ['UINT:11', 7],
						'created_at' => ['UINT:11', 0],
					],
					'PRIMARY_KEY' => 'comment_id',
					'KEYS' => [
						'event_id' => ['INDEX', 'event_id'],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'eventboard_comments',
				$this->table_prefix . 'eventboard_participants',
				$this->table_prefix . 'eventboard_events',
				$this->table_prefix . 'eventboard_categories',
			],
		];
	}
}
