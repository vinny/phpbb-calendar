<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\tests\migrations;

class database_test extends \phpbb_database_test_case
{
	protected $db_tools;
	protected $table_prefix;

	protected static function setup_extensions()
	{
		return array('vinny/calendar');
	}

	public function getDataSet()
	{
		return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../fixtures/add_database_changes.xml');
	}

	public function setUp(): void
	{
		parent::setUp();

		global $table_prefix;
		$this->table_prefix = $table_prefix;

		$db = $this->new_dbal();
		$factory = new \phpbb\db\tools\factory();
		$this->db_tools = $factory->get($db);
	}

	public function test_categories_table_exists()
	{
		$this->assertTrue(
			$this->db_tools->sql_table_exists($this->table_prefix . 'eventboard_categories'),
			'Asserting that eventboard_categories table exists'
		);
	}

	public function test_query_categories()
	{
		$db = $this->new_dbal();
		$sql = 'SELECT cat_name FROM ' . $this->table_prefix . 'eventboard_categories WHERE cat_id = 1';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$this->assertEquals('Test Category', $row['cat_name']);
	}
}
