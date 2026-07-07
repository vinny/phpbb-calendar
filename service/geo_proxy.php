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

class geo_proxy
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(\phpbb\config\config $config, \phpbb\user $user)
	{
		$this->config = $config;
		$this->user = $user;
	}

	public function autocomplete($text)
	{
		$text = trim((string) $text);
		$text = mb_substr($text, 0, 120);
		$api_key = (string) ($this->config['vinny_calendar_geoapify_key'] ?? '');
		$map_lang = (string) ($this->user->lang('MAP_LANG') ?: 'en');

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
			return ['features' => [], 'error' => $this->user->lang('EVENT_GEO_PROXY_FETCH_FAILED')];
		}

		$data = json_decode($result, true);
		if (!is_array($data) || empty($data['features']) || !is_array($data['features']))
		{
			return ['features' => [], 'error' => $this->user->lang('EVENT_GEO_PROXY_INVALID_JSON')];
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
		$options = [
			'http' => [
				'method' => 'GET',
				'header' => "User-Agent: phpBB-Calendar-Extension\r\n",
				'timeout' => 8,
			],
		];

		$result = false;

		if (ini_get('allow_url_fopen'))
		{
			$result = @file_get_contents($url, false, stream_context_create($options));
		}

		if ($result !== false)
		{
			return $result;
		}

		if (function_exists('curl_init'))
		{
			$ch = curl_init($url);
			curl_setopt_array($ch, [
				CURLOPT_CONNECTTIMEOUT => 5,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 8,
				CURLOPT_USERAGENT => 'phpBB-Calendar-Extension',
			]);

			$result = curl_exec($ch);
			$status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
			curl_close($ch);

			if ($result !== false && $status >= 200 && $status < 300)
			{
				return $result;
			}
		}

		return false;
	}
}
