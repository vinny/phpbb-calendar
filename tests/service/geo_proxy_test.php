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

class dummy_user extends \phpbb\user
{
	public function __construct() {}
	public function lang() { return 'en'; }
}

class geo_proxy_test extends \phpbb_test_case
{
	protected $config;
	protected $user;
	protected $service;

	public function setUp(): void
	{
		parent::setUp();

		$this->config = new \phpbb\config\config(['vinny_calendar_geoapify_key' => '']);
		$this->user = new dummy_user();

		$this->service = new \vinny\calendar\service\geo_proxy(
			$this->config,
			$this->user
		);
	}

	public function test_autocomplete_empty_text()
	{
		$result = $this->service->autocomplete('');
		$this->assertEquals(['features' => []], $result);
	}
}
