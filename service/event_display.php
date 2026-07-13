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

	public function truncate($text, $uid, $bitfield, $options, $limit = 200)
	{
		$desc_clean = strip_tags(generate_text_for_display($text, $uid, $bitfield, $options));

		if (mb_strlen($desc_clean) > $limit)
		{
			return mb_substr($desc_clean, 0, $limit) . '...';
		}

		return $desc_clean;
	}

	public function editable_text($text, $uid, $options)
	{
		$quote_data = generate_text_for_edit($text, $uid ?? '', $options ?? 7);

		return $quote_data['text'];
	}

	public function plain_text($text, $uid, $bitfield, $options)
	{
		$text = generate_text_for_display($text, $uid, $bitfield, $options);
		$text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
		$text = preg_replace('/\s+/u', ' ', $text);

		return trim($text);
	}
}
