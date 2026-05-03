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

class map_image
{
    /** @var \phpbb\config\config */
    protected $config;

    /** @var string */
    protected $root_path;

    public function __construct(\phpbb\config\config $config, $root_path)
    {
        $this->config = $config;
        $this->root_path = $root_path;
    }

    public function generate($event_id, $lat, $lng)
    {
        $lat = (float) $lat;
        $lng = (float) $lng;

        if ($lat == 0.0 || $lng == 0.0) {
            return '';
        }

        $api_key = $this->config['vinny_calendar_geoapify_key'] ?? '';
        if (!$api_key) {
            return '';
        }

        $width = (int) ($this->config['vinny_calendar_map_width'] ?? 1024);
        $height = (int) ($this->config['vinny_calendar_map_height'] ?? 768);
        $zoom = (int) ($this->config['vinny_calendar_map_zoom'] ?? 17);
        $map_lang = $this->config['vinny_calendar_map_lang'] ?? 'en';

        $url = 'https://maps.geoapify.com/v1/staticmap'
            . '?style=osm-carto'
            . '&width=' . $width
            . '&height=' . $height
            . '&center=lonlat:' . $lng . ',' . $lat
            . '&zoom=' . $zoom
            . '&lang=' . rawurlencode($map_lang)
            . '&marker=lonlat:' . $lng . ',' . $lat . ';type:material;color:red;icontype:awesome;icon:map-pin'
            . '&apiKey=' . rawurlencode($api_key);

        $image_data = @file_get_contents($url);
        if ($image_data === false) {
            return '';
        }

        $dir = $this->root_path . 'images/vinny_calendar_img/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $filename = 'event_' . (int) $event_id . '.png';
        if (@file_put_contents($dir . $filename, $image_data) === false) {
            return '';
        }

        return $filename;
    }
}
