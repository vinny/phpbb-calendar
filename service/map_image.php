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

class map_image
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var string */
	protected $root_path;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var \phpbb\language\language */
	protected $language;

	public function __construct(\phpbb\config\config $config, $root_path, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\filesystem\filesystem_interface $filesystem, \phpbb\language\language $language = null)
	{
		$this->config = $config;
		$this->root_path = $root_path;
		$this->user = $user;
		$this->auth = $auth;
		$this->filesystem = $filesystem;
		$this->language = $language ?: (isset($user->language) && is_object($user->language) ? $user->language : null);
	}

	public function generate($event_id, $lat, $lng)
	{
		if ($this->user->data['user_id'] == ANONYMOUS || !empty($this->user->data['is_bot']) || !$this->auth->acl_get('u_eventboard_create'))
		{
			return '';
		}

		$lat = (float) $lat;
		$lng = (float) $lng;

		if ($lat == 0.0 || $lng == 0.0)
		{
			return '';
		}

		$api_key = $this->config['vinny_calendar_geoapify_key'] ?? '';
		if (!$api_key)
		{
			return '';
		}

		$width = (int) $this->config['vinny_calendar_map_width'];
		$height = (int) $this->config['vinny_calendar_map_height'];
		$zoom = (int) $this->config['vinny_calendar_map_zoom'];
		$map_lang = ($this->language !== null) ? $this->language->lang('CALENDAR_MAP_LANG') : 'en';

		$url = 'https://maps.geoapify.com/v1/staticmap'
			. '?style=osm-carto'
			. '&width=' . $width
			. '&height=' . $height
			. '&center=lonlat:' . $lng . ',' . $lat
			. '&zoom=' . $zoom
			. '&lang=' . rawurlencode($map_lang)
			. '&marker=lonlat:' . $lng . ',' . $lat . ';type:material;color:red;icontype:awesome;icon:map-pin'
			. '&apiKey=' . rawurlencode($api_key);

		try
		{
			$client = new \GuzzleHttp\Client([
				'timeout' => 10.0,
			]);
			$response = $client->get($url);
			if ($response->getStatusCode() !== 200)
			{
				return '';
			}
			$image_data = (string) $response->getBody();
		}
		catch (\Exception $e)
		{
			return '';
		}

		$dir = $this->root_path . 'images/vinny_calendar_img/';
		if (!$this->filesystem->exists($dir))
		{
			try
			{
				$this->filesystem->mkdir($dir, 0755);
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				return '';
			}
		}

		try
		{
			$filename = bin2hex(random_bytes(16)) . '.png';
		}
		catch (\Exception $e)
		{
			$filename = substr(hash('sha256', uniqid((string) mt_rand(), true)), 0, 32) . '.png';
		}

		try
		{
			$this->filesystem->dump_file($dir . $filename, $image_data);
		}
		catch (\phpbb\filesystem\exception\filesystem_exception $e)
		{
			return '';
		}

		return $filename;
	}

	public function delete($file_or_id)
	{
		if (empty($file_or_id))
		{
			return;
		}

		$filename = is_numeric($file_or_id) ? 'event_' . (int) $file_or_id . '.png' : basename((string) $file_or_id);
		$file = $this->root_path . 'images/vinny_calendar_img/' . $filename;
		if ($this->filesystem->exists($file))
		{
			try
			{
				$this->filesystem->remove($file);
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				// Ignore filesystem exceptions on delete
			}
		}
	}
}
