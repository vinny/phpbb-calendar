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

class dummy_request extends \phpbb\request\request
{
	public function __construct() {}
}

class dummy_helper extends \phpbb\controller\helper
{
	public function __construct() {}
}

class dummy_event_access extends \vinny\calendar\service\event_access
{
	public function __construct() {}
}

class calendar_link_test extends \phpbb_test_case
{
	protected $helper;
	protected $request;
	protected $event_access;
	protected $service;

	public function setUp(): void
	{
		parent::setUp();

		$this->helper = new dummy_helper();
		$this->request = new dummy_request();
		$this->event_access = new dummy_event_access();

		$this->service = new \vinny\calendar\service\calendar_link(
			$this->helper,
			$this->request,
			$this->event_access
		);
	}

	public function test_absolute_url_with_http_route()
	{
		$route = 'http://example.com/route';
		$result = $this->service->absolute_url('http://localhost', $route);
		$this->assertEquals($route, $result);
	}

	public function test_absolute_url_with_relative_route()
	{
		$route = '/route/path';
		$result = $this->service->absolute_url('http://localhost/forum/', $route);
		$this->assertEquals('http://localhost/forum/route/path', $result);
	}
}
