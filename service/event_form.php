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

class event_form
{
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \vinny\calendar\service\event_access */
	protected $event_access;

	public function __construct(\phpbb\request\request $request, \phpbb\user $user, \phpbb\config\config $config, \vinny\calendar\service\event_access $event_access)
	{
		$this->request = $request;
		$this->user = $user;
		$this->config = $config;
		$this->event_access = $event_access;
	}

	public function build_create_payload()
	{
		list($subject, $start_at, $end_at, $loc, $desc, $cat_id, $lat, $lng, $visibility, $access_token, $max_participants) = $this->collect_common_fields();

		$uid = $bitfield = $options = '';
		$this->prepare_description($desc, $uid, $bitfield, $options);

		return [
			'user_id' => (int) $this->user->data['user_id'],
			'title' => $subject,
			'description' => $desc,
			'start_at' => $start_at,
			'end_at' => $end_at,
			'location' => $loc,
			'lat' => $lat,
			'lng' => $lng,
			'cat_id' => $cat_id,
			'max_participants' => $max_participants,
			'created_at' => time(),
			'visibility' => $visibility,
			'access_token' => $access_token,
			'desc_uid' => $uid,
			'desc_bitfield' => $bitfield,
			'desc_options' => $options,
		];
	}

	public function build_update_payload(array $event)
	{
		list($subject, $start_at, $end_at, $loc, $desc, $cat_id, $lat, $lng, $visibility, $access_token, $max_participants) = $this->collect_common_fields($event);

		$uid = $event['desc_uid'];
		$bitfield = $event['desc_bitfield'];
		$options = $event['desc_options'];
		$this->prepare_description($desc, $uid, $bitfield, $options);

		$map_image = $event['map_image'] ?: '';
		if (abs($lat - (float) $event['lat']) > 0.0001 || abs($lng - (float) $event['lng']) > 0.0001)
		{
			$map_image = '';
		}

		return [
			'title' => $subject,
			'description' => $desc,
			'start_at' => $start_at,
			'end_at' => $end_at,
			'location' => $loc,
			'lat' => $lat,
			'lng' => $lng,
			'cat_id' => $cat_id,
			'max_participants' => $max_participants,
			'visibility' => $visibility,
			'access_token' => $access_token,
			'desc_uid' => $uid,
			'desc_bitfield' => $bitfield,
			'desc_options' => $options,
			'map_image' => $map_image,
		];
	}

	protected function collect_common_fields(array $event = null)
	{
		$subject = trim($this->request->variable('event_subject', '', true));
		$start = trim($this->request->variable('event_start', ''));
		$end = trim($this->request->variable('event_end', ''));
		$loc = trim($this->request->variable('event_location', '', true));
		$desc = $this->request->variable('event_desc', '', true);
		$cat_id = (int) $this->request->variable('event_category', 0);
		$format = $this->request->variable('event_format', 'in-person');
		$lat = (float) $this->request->variable('event_lat', 0.0);
		$lng = (float) $this->request->variable('event_lng', 0.0);

		if ($subject === '')
		{
			trigger_error('EVENT_TITLE_REQUIRED');
		}

		if ($cat_id <= 0)
		{
			trigger_error('EVENT_CATEGORY_REQUIRED');
		}

		if (trim(strip_tags($desc)) === '')
		{
			trigger_error('EVENT_DESCRIPTION_REQUIRED');
		}

		if ($format === 'online')
		{
			$loc = '';
			$lat = 0.0;
			$lng = 0.0;
		}
		else if ($loc === '')
		{
			trigger_error('EVENT_LOCATION_REQUIRED');
		}

		$start_at = $this->parse_local_datetime($start);
		if (!$start_at)
		{
			trigger_error('EVENT_START_INVALID');
		}

		$end_at = $end ? $this->parse_local_datetime($end) : ($start_at + 3600);
		if (!$end_at || $end_at <= $start_at)
		{
			trigger_error('EVENT_END_INVALID');
		}

		$limit_enabled = $this->request->is_set_post('limit_registrations_enabled');
		$limit_val = (int) $this->request->variable('event_attendees_limit', 0);
		$max_participants = $limit_enabled ? $limit_val : 0;
		if ($limit_enabled && $max_participants <= 0)
		{
			trigger_error('EVENT_LIMIT_INVALID');
		}

		$public_cal = $this->request->is_set_post('public_calendar_enabled');
		$visibility = $public_cal ? 0 : 1;
		$access_token = '';

		if ($visibility === 1)
		{
			$access_token = $event ? $this->event_access->ensure_private_token($event) : $this->event_access->generate_private_token();
		}

		return [$subject, $start_at, $end_at, $loc, $desc, $cat_id, $lat, $lng, $visibility, $access_token, $max_participants];
	}

	protected function parse_local_datetime($value)
	{
		$value = trim((string) $value);
		if ($value === '')
		{
			return false;
		}

		$timestamp = $this->user->get_timestamp_from_format('Y-m-d H:i', $value);

		return ($timestamp !== false) ? (int) $timestamp : false;
	}

	protected function prepare_description(&$desc, &$uid, &$bitfield, &$options)
	{
		$allow_bbcode = ($this->config['allow_bbcode'] && $this->user->optionget('bbcode')) ? true : false;
		$allow_urls = ($this->config['allow_post_links']) ? true : false;
		$allow_smilies = ($this->config['allow_smilies'] && $this->user->optionget('smilies')) ? true : false;

		if ($this->request->is_set_post('disable_bbcode'))
		{
			$allow_bbcode = false;
		}
		if ($this->request->is_set_post('disable_smilies'))
		{
			$allow_smilies = false;
		}
		if ($this->request->is_set_post('disable_magic_url'))
		{
			$allow_urls = false;
		}

		generate_text_for_storage($desc, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);
	}
}
