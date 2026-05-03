<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license GPL-2.0-only
 *
 */

namespace vinny\calendar\service;

class feed
{
    public function build_rss(array $events, $feed_title, $feed_link, $feed_desc, $board_url, callable $event_url_builder)
    {
        $parts = parse_url($board_url);
        $root_url = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $root_url .= ':' . $parts['port'];
        }

        $items = '';
        foreach ($events as $row) {
            $view_url = htmlspecialchars($root_url . $event_url_builder($row), ENT_XML1, 'UTF-8');
            $title = htmlspecialchars(html_entity_decode($row['title']), ENT_XML1, 'UTF-8');
            $desc = htmlspecialchars(strip_tags(html_entity_decode($row['description'])), ENT_XML1, 'UTF-8');
            $category = htmlspecialchars($row['cat_name'] ?: 'Event', ENT_XML1, 'UTF-8');
            $author = htmlspecialchars($row['username'], ENT_XML1, 'UTF-8');

            $items .= "<item>
                <title>$title</title>
                <link>$view_url</link>
                <guid>$view_url</guid>
                <pubDate>" . date('r', $row['start_at']) . "</pubDate>
                <description>$desc</description>
                <category>$category</category>
                <dc:creator>$author</dc:creator>
            </item>";
        }

        return '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>' . htmlspecialchars($feed_title, ENT_XML1, 'UTF-8') . '</title>
        <link>' . htmlspecialchars($feed_link, ENT_XML1, 'UTF-8') . '</link>
        <description>' . htmlspecialchars($feed_desc, ENT_XML1, 'UTF-8') . '</description>
        <lastBuildDate>' . date('r') . '</lastBuildDate>
        ' . $items . '
    </channel>
</rss>';
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

        foreach ($events as $row) {
            $desc_clean = strip_tags(generate_text_for_display($row['description'], $row['desc_uid'], $row['desc_bitfield'], $row['desc_options']));

            $vcalendar .= "BEGIN:VEVENT\r\n";
            $vcalendar .= "UID:event-" . $row['event_id'] . "@" . $host . "\r\n";
            $vcalendar .= "DTSTAMP:" . gmdate('Ymd\THis\Z', time()) . "\r\n";
            $vcalendar .= "DTSTART:" . gmdate('Ymd\THis\Z', $row['start_at']) . "\r\n";
            $vcalendar .= "DTEND:" . gmdate('Ymd\THis\Z', $row['end_at']) . "\r\n";
            $vcalendar .= $this->format_ical_line('SUMMARY', $row['title']);
            $vcalendar .= $this->format_ical_line('DESCRIPTION', $desc_clean);
            if (!empty($row['location'])) {
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

        while (strlen($line) > 75) {
            $folded .= mb_strcut($line, 0, 75) . "\r\n ";
            $line = mb_strcut($line, 75);
        }

        return $folded . $line . "\r\n";
    }
}
