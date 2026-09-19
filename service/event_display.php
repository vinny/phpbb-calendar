<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\calendar\service;

class event_display
{
	/** @var \phpbb\user */
	protected $user;

	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
	}

	public function is_online(array $event)
	{
		return empty($event['location']) || trim(strtolower($event['location'])) === 'online';
	}

	public function editable_text($text, $uid, $options)
	{
		$quote_data = generate_text_for_edit($text, $uid ?? '', $options ?? 7);

		return $quote_data['text'];
	}

	public function plain_text($text, $uid, $bitfield, $options)
	{
		$text = generate_text_for_display($text, $uid, $bitfield, $options);
		$text = strip_tags($text);
		$text = preg_replace('/\s+/u', ' ', $text);

		return trim($text);
	}
}
