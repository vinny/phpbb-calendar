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

class geo_proxy
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\language\language */
	protected $language;

	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \phpbb\cache\driver\driver_interface $cache, \phpbb\language\language $language = null)
	{
		$this->config = $config;
		$this->user = $user;
		$this->cache = $cache;
		$this->language = $language ?: (isset($user->language) && is_object($user->language) ? $user->language : null);
	}

	public function is_rate_limited($session_id = null)
	{
		$session_id = ($session_id !== null) ? (string) $session_id : (string) ($this->user->data['session_id'] ?? '');
		if ($session_id === '')
		{
			return false;
		}

		$cache_key = '_geo_proxy_rl_' . substr(md5($session_id), 0, 16);
		$now = time();
		$data = $this->cache->get($cache_key);

		if (!is_array($data) || ($now - (int) ($data['start'] ?? 0)) >= 60)
		{
			$data = ['start' => $now, 'count' => 1];
			$this->cache->put($cache_key, $data, 60);
			return false;
		}

		if ((int) $data['count'] >= 30)
		{
			return true;
		}

		$data['count'] = (int) $data['count'] + 1;
		$ttl = max(1, 60 - ($now - (int) $data['start']));
		$this->cache->put($cache_key, $data, $ttl);

		return false;
	}

	public function autocomplete($text)
	{
		$text = trim((string) $text);
		$text = mb_substr($text, 0, 120);
		$api_key = (string) ($this->config['vinny_calendar_geoapify_key'] ?? '');
		$map_lang = (string) (($this->language ? $this->language->lang('CALENDAR_MAP_LANG') : 'en') ?: 'en');

		if ($text === '' || mb_strlen($text) < 2 || $api_key === '')
		{
			return ['features' => []];
		}

		$url = 'https://api.geoapify.com/v1/geocode/autocomplete'
			. '?text=' . urlencode($text)
			. '&apiKey=' . urlencode($api_key)
			. '&lang=' . urlencode($map_lang)
			. '&limit=5';

		$result = $this->fetch_url($url);

		if ($result === false)
		{
			$error_msg = $this->language ? $this->language->lang('EVENT_GEO_PROXY_FETCH_FAILED') : 'Fetch failed';
			return ['features' => [], 'error' => $error_msg];
		}

		$data = json_decode($result, true);
		if (!is_array($data) || empty($data['features']) || !is_array($data['features']))
		{
			$error_msg = $this->language ? $this->language->lang('EVENT_GEO_PROXY_INVALID_JSON') : 'Invalid JSON';
			return ['features' => [], 'error' => $error_msg];
		}

		$features = [];
		foreach (array_slice($data['features'], 0, 5) as $feature)
		{
			if (empty($feature['properties']) || !is_array($feature['properties']))
			{
				continue;
			}

			$properties = $feature['properties'];
			$features[] = [
				'formatted' => (string) ($properties['formatted'] ?? ''),
				'lat' => isset($properties['lat']) ? (float) $properties['lat'] : 0,
				'lon' => isset($properties['lon']) ? (float) $properties['lon'] : 0,
			];
		}

		return ['features' => $features];
	}

	protected function fetch_url($url)
	{
		try
		{
			$client = new \GuzzleHttp\Client([
				'timeout' => 8.0,
				'headers' => [
					'User-Agent' => 'phpBB-Calendar-Extension',
				],
			]);
			$response = $client->get($url);

			return (string) $response->getBody();
		}
		catch (\Exception $e)
		{
			return false;
		}
	}
}
