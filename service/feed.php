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

class feed
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	public function __construct(\phpbb\user $user, \phpbb\language\language $language)
	{
		$this->user = $user;
		$this->language = $language;
	}

	public function build_atom(array $events, $feed_title, $feed_link, $feed_desc, $board_url, callable $event_url_builder)
	{
		$parts = parse_url($board_url);
		$root_url = $parts['scheme'] . '://' . $parts['host'];
		if (!empty($parts['port']))
		{
			$root_url .= ':' . $parts['port'];
		}

		$xml_lang = utf8_htmlspecialchars($this->user->lang_name);

		$entries = '';
		$dt = $this->user->create_datetime();

		foreach ($events as $row)
		{
			$view_url = utf8_htmlspecialchars($root_url . $event_url_builder($row));
			$title = utf8_htmlspecialchars(html_entity_decode($row['title'], ENT_QUOTES, 'UTF-8'));

			$desc_html = generate_text_for_display($row['description'], $row['desc_uid'], $row['desc_bitfield'], $row['desc_options']);
			$desc_html = preg_replace('#<br\s*/?>#i', "\n", $desc_html);
			$desc_text = trim(html_entity_decode(strip_tags($desc_html), ENT_QUOTES, 'UTF-8'));

			$start_date_str = $this->user->format_date($row['start_at']);
			$end_date_str = $this->user->format_date($row['end_at']);

			$start_label = $this->language->lang('EVENT_START');
			$end_label = $this->language->lang('EVENT_END');
			$loc_label = $this->language->lang('EVENT_LOCATION');
			$uncat_label = $this->language->lang('UNCATEGORIZED');

			$raw_loc = trim((string) ($row['location'] ?? ''));
			if ($raw_loc === '' || strtolower($raw_loc) === 'online')
			{
				$location_str = $this->language->lang('EVENT_ONLINE');
			}
			else
			{
				$location_str = html_entity_decode($raw_loc, ENT_QUOTES, 'UTF-8');
			}

			$meta_lines = [
				$start_label . ': ' . $start_date_str,
				$end_label . ': ' . $end_date_str,
				$loc_label . ': ' . $location_str,
			];

			$full_content = implode("\n", $meta_lines);
			if ($desc_text !== '')
			{
				$full_content .= "\n\n" . $desc_text;
			}
			$full_content = utf8_htmlspecialchars($full_content);

			$category_raw = !empty($row['cat_name']) ? html_entity_decode($row['cat_name'], ENT_QUOTES, 'UTF-8') : $uncat_label;
			$category = utf8_htmlspecialchars($category_raw);
			$author = utf8_htmlspecialchars(html_entity_decode($row['username'], ENT_QUOTES, 'UTF-8'));

			// Format dates (using created_at to avoid future dates)
			$published_time = !empty($row['created_at']) ? (int) $row['created_at'] : time();
			$dt->setTimestamp($published_time);
			$published_str = $dt->format(\DateTime::ATOM);

			$entries .= '		<entry>
			<author><name>' . $author . '</name></author>
			<updated>' . $published_str . '</updated>
			<published>' . $published_str . '</published>
			<id>' . $view_url . '</id>
			<link href="' . $view_url . '" />
			<title>' . $title . '</title>
			<category term="' . $category . '" label="' . $category . '" />
			<content type="text">' . $full_content . '</content>
		</entry>
';
		}

		$feed_updated = $this->user->create_datetime()->setTimestamp(time())->format(\DateTime::ATOM);

		return '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="' . $xml_lang . '">
	<link rel="self" type="application/atom+xml" href="' . utf8_htmlspecialchars($feed_link) . '" />
	<title>' . utf8_htmlspecialchars(html_entity_decode($feed_title, ENT_QUOTES, 'UTF-8')) . '</title>
	<subtitle>' . utf8_htmlspecialchars(html_entity_decode($feed_desc, ENT_QUOTES, 'UTF-8')) . '</subtitle>
	<link href="' . utf8_htmlspecialchars($feed_link) . '" />
	<updated>' . $feed_updated . '</updated>
	<id>' . utf8_htmlspecialchars($feed_link) . '</id>
' . $entries . '</feed>';
	}



	public function build_ical(array $events, $calendar_name, $timezone, $host, callable $event_url_builder)
	{
		$vcalendar = "BEGIN:VCALENDAR\r\n";
		$vcalendar .= "VERSION:2.0\r\n";
		$vcalendar .= "PRODID:-//phpBB EventBoard//EN\r\n";
		$vcalendar .= "CALSCALE:GREGORIAN\r\n";
		$vcalendar .= "METHOD:PUBLISH\r\n";
		$vcalendar .= $this->format_ical_line('X-WR-CALNAME', html_entity_decode($calendar_name, ENT_QUOTES, 'UTF-8'));
		$vcalendar .= $this->format_ical_line('X-WR-TIMEZONE', $timezone);

		foreach ($events as $row)
		{
			$desc_clean = strip_tags(generate_text_for_display($row['description'], $row['desc_uid'], $row['desc_bitfield'], $row['desc_options']));

			$vcalendar .= "BEGIN:VEVENT\r\n";
			$vcalendar .= "UID:event-" . $row['event_id'] . "@" . $host . "\r\n";
			$vcalendar .= "DTSTAMP:" . gmdate('Ymd\THis\Z', time()) . "\r\n";
			$vcalendar .= "DTSTART:" . gmdate('Ymd\THis\Z', $row['start_at']) . "\r\n";
			$vcalendar .= "DTEND:" . gmdate('Ymd\THis\Z', $row['end_at']) . "\r\n";
			$vcalendar .= $this->format_ical_line('SUMMARY', html_entity_decode($row['title'], ENT_QUOTES, 'UTF-8'));
			$vcalendar .= $this->format_ical_line('DESCRIPTION', html_entity_decode($desc_clean, ENT_QUOTES, 'UTF-8'));
			if (!empty($row['location']))
			{
				$vcalendar .= $this->format_ical_line('LOCATION', html_entity_decode($row['location'], ENT_QUOTES, 'UTF-8'));
			}
			$vcalendar .= "URL:" . $event_url_builder($row) . "\r\n";
			$vcalendar .= "END:VEVENT\r\n";
		}

		$vcalendar .= "END:VCALENDAR\r\n";

		return $vcalendar;
	}

	protected function format_ical_line($key, $value)
	{
		$value = str_replace('\\', '\\\\', $value);
		$value = str_replace(',', '\,', $value);
		$value = str_replace(';', '\;', $value);
		$value = str_replace(["\r\n", "\n", "\r"], '\n', $value);

		$line = $key . ':' . $value;
		$folded = '';

		while (strlen($line) > 75)
		{
			$folded .= mb_strcut($line, 0, 75) . "\r\n ";
			$line = mb_strcut($line, 75);
		}

		return $folded . $line . "\r\n";
	}
}
