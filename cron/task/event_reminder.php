<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\cron\task;

class event_reminder extends \phpbb\cron\task\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \vinny\calendar\service\event_reminder */
	protected $event_reminder;

	public function __construct(\phpbb\config\config $config, \vinny\calendar\service\event_reminder $event_reminder)
	{
		$this->config = $config;
		$this->event_reminder = $event_reminder;
		$this->set_name('vinny.calendar.cron.task.event_reminder');
	}

	public function is_runnable()
	{
		return $this->event_reminder->is_enabled();
	}

	public function should_run()
	{
		return ((int) $this->config['vinny_calendar_reminder_last_run'] + 60) <= time();
	}

	public function run()
	{
		$this->config->set('vinny_calendar_reminder_last_run', time());
		$this->event_reminder->dispatch_due_reminders();
		return null;
	}
}
