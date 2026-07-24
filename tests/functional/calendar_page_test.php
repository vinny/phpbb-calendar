<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\tests\functional;

class calendar_page_test extends \phpbb_functional_test_case
{
	protected static function setup_extensions()
	{
		return array('vinny/calendar');
	}

	public function test_calendar_page_response()
	{
		$this->login();
		$crawler = $this->request('GET', 'app.php/events');
		$this->assertStringContainsString('Calendar', $this->get_content());
	}
}
