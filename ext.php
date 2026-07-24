<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar;

/**
 * EventBoard extension class.
 */
class ext extends \phpbb\extension\base
{
	/**
	 * Enable notifications and create images/vinny_calendar_img when extension is enabled
	 */
	public function enable_step($old_state)
	{
		if ($old_state === false)
		{
			/** @var \phpbb\notification\manager $notification_manager */
			$notification_manager = $this->container->get('notification_manager');
			$notification_manager->enable_notifications('vinny.calendar.notification.type.participant_added');
			$notification_manager->enable_notifications('vinny.calendar.notification.type.new_comment');
			$notification_manager->enable_notifications('vinny.calendar.notification.type.event_reminder');
			return 'notifications_enabled';
		}

		if ($old_state === 'notifications_enabled')
		{
			$filesystem = $this->container->get('filesystem');
			$my_dir_path = $this->container->getParameter('core.root_path') . 'images/vinny_calendar_img';

			try
			{
				$filesystem->mkdir($my_dir_path, 0755);
				$filesystem->touch($my_dir_path . '/index.htm');
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				// Intentionally ignored.
			}

			return 'added vinny_calendar_img_dir';
		}

		return parent::enable_step($old_state);
	}

	/**
	 * Disable notifications for the extension.
	 */
	public function disable_step($old_state)
	{
		if ($old_state === false)
		{
			/** @var \phpbb\notification\manager $notification_manager */
			$notification_manager = $this->container->get('notification_manager');
			$notification_manager->disable_notifications('vinny.calendar.notification.type.participant_added');
			$notification_manager->disable_notifications('vinny.calendar.notification.type.new_comment');
			$notification_manager->disable_notifications('vinny.calendar.notification.type.event_reminder');
			return 'notifications_disabled';
		}

		return parent::disable_step($old_state);
	}

	/**
	 * Delete images/vinny_calendar_img and purge notifications when deleting extension data
	 */
	public function purge_step($old_state)
	{
		if ($old_state === false)
		{
			/** @var \phpbb\notification\manager $notification_manager */
			$notification_manager = $this->container->get('notification_manager');
			$notification_manager->purge_notifications('vinny.calendar.notification.type.participant_added');
			$notification_manager->purge_notifications('vinny.calendar.notification.type.new_comment');
			$notification_manager->purge_notifications('vinny.calendar.notification.type.event_reminder');
			return 'notifications_purged';
		}

		if ($old_state === 'notifications_purged')
		{
			$filesystem = $this->container->get('filesystem');
			$my_dir_path = $this->container->getParameter('core.root_path') . 'images/vinny_calendar_img';

			try
			{
				$filesystem->remove($my_dir_path);
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				// Intentionally ignored.
			}

			return 'removed vinny_calendar_img_dir';
		}

		return parent::purge_step($old_state);
	}
}
