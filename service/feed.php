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

	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
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
			$title = utf8_htmlspecialchars(html_entity_decode($row['title']));

			// Format HTML content
			$desc_html = generate_text_for_display($row['description'], $row['desc_uid'], $row['desc_bitfield'], $row['desc_options']);

			$start_date_str = $this->user->format_date($row['start_at']);
			$end_date_str = $this->user->format_date($row['end_at']);
			$location_str = !empty($row['location']) ? utf8_htmlspecialchars($row['location']) : '';

			$meta_html = '<p><strong>' . $this->user->lang('EVENT_START') . ':</strong> ' . $start_date_str . '<br />';
			$meta_html .= '<strong>' . $this->user->lang('EVENT_END') . ':</strong> ' . $end_date_str . '<br />';
			if (!empty($location_str))
			{
				$meta_html .= '<strong>' . $this->user->lang('EVENT_LOCATION') . ':</strong> ' . $location_str . '<br />';
			}
			$meta_html .= '</p><hr />';

			$full_content = $meta_html . $desc_html;

			$category = utf8_htmlspecialchars($row['cat_name'] ?: 'Event');
			$author = utf8_htmlspecialchars($row['username']);

			// Format dates (using created_at to avoid future dates)
			$published_time = !empty($row['created_at']) ? (int) $row['created_at'] : time();
			$dt->setTimestamp($published_time);
			$published_str = $dt->format(\DateTime::ATOM);

			$entries .= '        <entry>
            <author><name><![CDATA[' . $author . ']]></name></author>
            <updated>' . $published_str . '</updated>
            <published>' . $published_str . '</published>
            <id>' . $view_url . '</id>
            <link href="' . $view_url . '" />
            <title type="html"><![CDATA[' . $title . ']]></title>
            <category term="' . $category . '" label="' . $category . '" />
            <content type="html" xml:base="' . $view_url . '"><![CDATA[' . $full_content . ']]></content>
        </entry>
';
		}

		$feed_updated = $this->user->create_datetime()->setTimestamp(time())->format(\DateTime::ATOM);

		return '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="' . $xml_lang . '">
    <link rel="self" type="application/atom+xml" href="' . utf8_htmlspecialchars($feed_link) . '" />
    <title>' . utf8_htmlspecialchars($feed_title) . '</title>
    <subtitle>' . utf8_htmlspecialchars($feed_desc) . '</subtitle>
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
		$vcalendar .= "X-WR-CALNAME:" . $calendar_name . "\r\n";
		$vcalendar .= "X-WR-TIMEZONE:" . $timezone . "\r\n";

		foreach ($events as $row)
		{
			$desc_clean = strip_tags(generate_text_for_display($row['description'], $row['desc_uid'], $row['desc_bitfield'], $row['desc_options']));

			$vcalendar .= "BEGIN:VEVENT\r\n";
			$vcalendar .= "UID:event-" . $row['event_id'] . "@" . $host . "\r\n";
			$vcalendar .= "DTSTAMP:" . gmdate('Ymd\THis\Z', time()) . "\r\n";
			$vcalendar .= "DTSTART:" . gmdate('Ymd\THis\Z', $row['start_at']) . "\r\n";
			$vcalendar .= "DTEND:" . gmdate('Ymd\THis\Z', $row['end_at']) . "\r\n";
			$vcalendar .= $this->format_ical_line('SUMMARY', $row['title']);
			$vcalendar .= $this->format_ical_line('DESCRIPTION', $desc_clean);
			if (!empty($row['location']))
			{
				$vcalendar .= $this->format_ical_line('LOCATION', $row['location']);
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
