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

class share
{
	public function build_targets($absolute_url, $title)
	{
		$url = rawurlencode($absolute_url);
		$text = rawurlencode($title);

		return [
			'copy' => $absolute_url,
			'whatsapp' => 'https://wa.me/?text=' . rawurlencode($title . ' ' . $absolute_url),
			'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
			'twitter' => 'https://twitter.com/intent/tweet?text=' . $text . '&url=' . $url,
			'telegram' => 'https://t.me/share/url?url=' . $url . '&text=' . $text,
		];
	}
}
