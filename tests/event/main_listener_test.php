<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\tests\event;

class main_listener_test extends \phpbb_test_case
{
	protected $user;
	protected $helper;
	protected $template;
	protected $config;
	protected $auth;
	protected $calendar_link;
	protected $event_query;
	protected $event_access;
	protected $listener;

	public function setUp(): void
	{
		parent::setUp();

		$this->user = $this->createMock('\phpbb\user');
		$this->user->data = ['user_id' => 2];
		$this->user->method('lang')->willReturnCallback(function () {
			$args = func_get_args();
			$key = array_shift($args);

			$lang_map = [
				'VIEWING_EVENT_CALENDAR'  => 'Viewing event calendar',
				'VIEWING_EVENT'           => 'Viewing event: %s',
				'CREATING_EVENT'          => 'Creating an event',
				'EDITING_EVENT'           => 'Editing an event',
				'VIEWING_UPCOMING_EVENTS' => 'Viewing upcoming events',
				'VIEWING_EVENT_CATEGORY'  => 'Viewing event category: %s',
				'VIEWING_MY_EVENTS'       => 'Viewing my events',
				'VIEWING_MY_RSVPS'        => 'Viewing my RSVPs',
			];

			$translation = $lang_map[$key] ?? $key;

			return empty($args) ? $translation : sprintf($translation, ...$args);
		});

		$this->helper = $this->createMock('\phpbb\controller\helper');
		$this->helper->method('route')->willReturnCallback(function ($route, $params = []) {
			return 'http://example.com/' . $route;
		});

		$this->template = $this->createMock('\phpbb\template\template');
		$this->config = new \phpbb\config\config(['vinny_calendar_enable' => 1]);

		$this->auth = $this->createMock('\phpbb\auth\auth');
		$this->auth->method('acl_get')->willReturn(true);

		$this->calendar_link = $this->createMock('\vinny\calendar\service\calendar_link');
		$this->calendar_link->method('route')->willReturnCallback(function ($route, $data, $params = []) {
			return 'http://example.com/' . $route . '/' . ($data['event_id'] ?? 0);
		});

		$this->event_query = $this->createMock('\vinny\calendar\service\event_query');
		$this->event_access = $this->createMock('\vinny\calendar\service\event_access');

		$this->listener = new \vinny\calendar\event\main_listener(
			$this->user,
			$this->helper,
			$this->template,
			$this->config,
			$this->auth,
			$this->calendar_link,
			$this->event_query,
			$this->event_access
		);
	}

	public function test_getSubscribedEvents()
	{
		$events = \vinny\calendar\event\main_listener::getSubscribedEvents();
		$this->assertArrayHasKey('core.viewonline_overwrite_location', $events);
		$this->assertEquals('viewonline_overwrite_location', $events['core.viewonline_overwrite_location']);
	}

	public function test_viewonline_disabled()
	{
		$this->config['vinny_calendar_enable'] = 0;

		$event = new \ArrayObject([
			'on_page' => [1 => 'app.php/events'],
			'row' => ['session_page' => 'app.php/events'],
			'location' => 'Original',
			'location_url' => 'http://example.com/original',
		]);

		$this->listener->viewonline_overwrite_location($event);

		$this->assertEquals('Original', $event['location']);
	}

	public function test_viewonline_calendar_index()
	{
		$event = new \ArrayObject([
			'on_page' => [1 => 'app.php/events'],
			'row' => ['session_page' => 'app.php/events'],
			'location' => 'Original',
			'location_url' => 'http://example.com/original',
		]);

		$this->listener->viewonline_overwrite_location($event);

		$this->assertEquals('Viewing event calendar', $event['location']);
		$this->assertEquals('http://example.com/vinny_calendar_controller', $event['location_url']);
	}

	public function test_viewonline_public_event()
	{
		$event_data = ['event_id' => 10, 'title' => 'Community Meeting', 'visibility' => 0];

		$this->event_query->expects($this->once())
			->method('get_event_basic')
			->with(10)
			->willReturn($event_data);

		$this->event_access->expects($this->once())
			->method('can_view_event')
			->with($event_data, 2, '')
			->willReturn(true);

		$this->event_access->expects($this->once())
			->method('build_route_params')
			->with($event_data, ['id' => 10])
			->willReturn(['id' => 10]);

		$event = new \ArrayObject([
			'on_page' => [1 => 'app.php/events'],
			'row' => ['session_page' => 'app.php/events/view/10'],
			'location' => 'Original',
			'location_url' => 'http://example.com/original',
		]);

		$this->listener->viewonline_overwrite_location($event);

		$this->assertEquals('Viewing event: Community Meeting', $event['location']);
		$this->assertEquals('http://example.com/vinny_calendar_view/10', $event['location_url']);
	}

	public function test_viewonline_private_event_access_denied()
	{
		$event_data = ['event_id' => 20, 'title' => 'Secret Event', 'visibility' => 1];

		$this->event_query->expects($this->once())
			->method('get_event_basic')
			->with(20)
			->willReturn($event_data);

		$this->event_access->expects($this->once())
			->method('can_view_event')
			->with($event_data, 2, '')
			->willReturn(false);

		$event = new \ArrayObject([
			'on_page' => [1 => 'app.php/events'],
			'row' => ['session_page' => 'app.php/events/view/20'],
			'location' => 'Original',
			'location_url' => 'http://example.com/original',
		]);

		$this->listener->viewonline_overwrite_location($event);

		$this->assertEquals('Viewing event calendar', $event['location']);
		$this->assertEquals('http://example.com/vinny_calendar_controller', $event['location_url']);
	}

	public function test_viewonline_create_event()
	{
		$event = new \ArrayObject([
			'on_page' => [1 => 'app.php/events'],
			'row' => ['session_page' => 'app.php/events/create'],
			'location' => 'Original',
			'location_url' => 'http://example.com/original',
		]);

		$this->listener->viewonline_overwrite_location($event);

		$this->assertEquals('Creating an event', $event['location']);
		$this->assertEquals('http://example.com/vinny_calendar_controller', $event['location_url']);
	}

	public function test_viewonline_edit_event()
	{
		$event = new \ArrayObject([
			'on_page' => [1 => 'app.php/events'],
			'row' => ['session_page' => 'app.php/events/edit/5'],
			'location' => 'Original',
			'location_url' => 'http://example.com/original',
		]);

		$this->listener->viewonline_overwrite_location($event);

		$this->assertEquals('Editing an event', $event['location']);
		$this->assertEquals('http://example.com/vinny_calendar_controller', $event['location_url']);
	}

	public function test_ucp_notifications_output_template_vars()
	{
		$event = new \ArrayObject([
			'type_data' => ['type' => 'vinny.calendar.notification.type.event_reminder'],
			'method_data' => ['id' => 'notification.method.email'],
			'subscriptions' => [],
			'tpl_ary' => ['SUBSCRIBED' => true],
		]);

		$this->listener->ucp_notifications_output_template_vars($event);

		$this->assertFalse($event['tpl_ary']['SUBSCRIBED']);
	}
}
