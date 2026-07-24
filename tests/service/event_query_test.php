<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\tests\service;

class event_query_test extends \phpbb_database_test_case
{
	protected $table_prefix;
	protected $event_query;

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
		$this->event_query = new \vinny\calendar\service\event_query($db, $this->table_prefix);

		$db->sql_query('DELETE FROM ' . $this->table_prefix . 'eventboard_events');

		$time = time();

		// Add occurring event
		$db->sql_query('INSERT INTO ' . $this->table_prefix . 'eventboard_events (user_id, cat_id, title, description, desc_uid, desc_bitfield, location, access_token, map_image, start_at, end_at, visibility)
			VALUES (2, 1, \'Occurring Event\', \'\', \'\', \'\', \'\', \'\', \'\', ' . ($time - 3600) . ', ' . ($time + 3600) . ', 0)');

		// Add upcoming event
		$db->sql_query('INSERT INTO ' . $this->table_prefix . 'eventboard_events (user_id, cat_id, title, description, desc_uid, desc_bitfield, location, access_token, map_image, start_at, end_at, visibility)
			VALUES (2, 1, \'Upcoming Event\', \'\', \'\', \'\', \'\', \'\', \'\', ' . ($time + 86400) . ', ' . ($time + 90000) . ', 0)');

		// Add ended event
		$db->sql_query('INSERT INTO ' . $this->table_prefix . 'eventboard_events (user_id, cat_id, title, description, desc_uid, desc_bitfield, location, access_token, map_image, start_at, end_at, visibility)
			VALUES (2, 1, \'Ended Event\', \'\', \'\', \'\', \'\', \'\', \'\', ' . ($time - 90000) . ', ' . ($time - 86400) . ', 0)');
	}

	public function test_get_occurring_public_events()
	{
		$events = $this->event_query->get_occurring_public_events();
		$this->assertCount(1, $events);
		$this->assertEquals('Occurring Event', $events[0]['title']);
	}

	public function test_get_upcoming_public_events()
	{
		$events = $this->event_query->get_upcoming_public_events(5);
		$this->assertCount(1, $events);
		$this->assertEquals('Upcoming Event', $events[0]['title']);
	}

	public function test_get_total_events_count()
	{
		$count = $this->event_query->get_total_events_count();
		$this->assertEquals(3, $count);
	}
}
