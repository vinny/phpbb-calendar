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

class geo_proxy_test extends \phpbb_test_case
{
	protected $config;
	protected $user;
	protected $cache;
	protected $service;

	public function setUp(): void
	{
		parent::setUp();

		$this->config = new \phpbb\config\config(['vinny_calendar_geoapify_key' => '']);
		$this->user = $this->getMockBuilder(\phpbb\user::class)
			->disableOriginalConstructor()
			->getMock();

		$this->user->method('lang')
			->willReturn('en');

		$this->cache = $this->getMockBuilder(\phpbb\cache\driver\driver_interface::class)
			->getMock();

		$this->service = new \vinny\calendar\service\geo_proxy(
			$this->config,
			$this->user,
			$this->cache
		);
	}

	public function test_autocomplete_empty_text()
	{
		$result = $this->service->autocomplete('');
		$this->assertEquals(['features' => []], $result);
	}

	public function test_is_rate_limited()
	{
		$session_id = 'test_session_123';
		$cache_key = '_geo_proxy_rl_' . substr(md5($session_id), 0, 16);

		$this->cache->expects($this->once())
			->method('get')
			->with($cache_key)
			->willReturn(['count' => 30, 'start' => time()]);

		$this->assertTrue($this->service->is_rate_limited($session_id));
	}

	public function test_is_not_rate_limited_empty_session()
	{
		$this->assertFalse($this->service->is_rate_limited(''));
	}
}
