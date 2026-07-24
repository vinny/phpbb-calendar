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

class calendar_add
{
	public function build_targets(array $event, $absolute_url, $description, $location)
	{
		$title = (string) $event['title'];
		$start_at = (int) $event['start_at'];
		$end_at = (int) $event['end_at'];
		$body = trim($description . "\n\n" . $absolute_url);
		$location = (string) $location;

		return [
			'google' => 'https://calendar.google.com/calendar/render?action=TEMPLATE'
				. '&text=' . rawurlencode($title)
				. '&dates=' . gmdate('Ymd\THis\Z', $start_at) . '/' . gmdate('Ymd\THis\Z', $end_at)
				. '&details=' . rawurlencode($body)
				. '&location=' . rawurlencode($location),
			'outlook' => 'https://outlook.live.com/calendar/0/action/compose?rru=addevent'
				. '&subject=' . rawurlencode($title)
				. '&startdt=' . rawurlencode(gmdate('Y-m-d\TH:i:s\Z', $start_at))
				. '&enddt=' . rawurlencode(gmdate('Y-m-d\TH:i:s\Z', $end_at))
				. '&body=' . rawurlencode($body)
				. '&location=' . rawurlencode($location),
			'yahoo' => 'https://calendar.yahoo.com/?v=60&view=d&type=20'
				. '&title=' . rawurlencode($title)
				. '&st=' . gmdate('Ymd\THis\Z', $start_at)
				. '&et=' . gmdate('Ymd\THis\Z', $end_at)
				. '&desc=' . rawurlencode($body)
				. '&in_loc=' . rawurlencode($location),
		];
	}
}
