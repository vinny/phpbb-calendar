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

class event_form
{
    /** @var \phpbb\request\request */
    protected $request;

    /** @var \phpbb\user */
    protected $user;

    /** @var \vinny\calendar\service\event_access */
    protected $event_access;

    public function __construct(\phpbb\request\request $request, \phpbb\user $user, \vinny\calendar\service\event_access $event_access)
    {
        $this->request = $request;
        $this->user = $user;
        $this->event_access = $event_access;
    }

    public function build_create_payload()
    {
        list($subject, $start, $end, $loc, $desc, $cat_id, $lat, $lng, $visibility, $access_token, $max_participants) = $this->collect_common_fields();

        $uid = $bitfield = $options = '';
        $this->prepare_description($desc, $uid, $bitfield, $options);

        return [
            'user_id' => (int) $this->user->data['user_id'],
            'title' => $subject,
            'description' => $desc,
            'start_at' => strtotime($start),
            'end_at' => $end ? strtotime($end) : strtotime($start) + 3600,
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
        list($subject, $start, $end, $loc, $desc, $cat_id, $lat, $lng, $visibility, $access_token, $max_participants) = $this->collect_common_fields($event);

        $uid = $event['desc_uid'];
        $bitfield = $event['desc_bitfield'];
        $options = $event['desc_options'];
        $this->prepare_description($desc, $uid, $bitfield, $options);

        $map_image = $event['map_image'] ?: '';
        if (abs($lat - (float) $event['lat']) > 0.0001 || abs($lng - (float) $event['lng']) > 0.0001) {
            $map_image = '';
        }

        return [
            'title' => $subject,
            'description' => $desc,
            'start_at' => strtotime($start),
            'end_at' => $end ? strtotime($end) : strtotime($start) + 3600,
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
        $subject = $this->request->variable('event_subject', '', true);
        $start = $this->request->variable('event_start', '');
        $end = $this->request->variable('event_end', '');
        $loc = $this->request->variable('event_location', '', true);
        $desc = $this->request->variable('event_desc', '', true);
        $cat_id = $this->request->variable('event_category', 0);
        $format = $this->request->variable('event_format', 'in-person');
        $lat = (float) $this->request->variable('event_lat', 0.0);
        $lng = (float) $this->request->variable('event_lng', 0.0);

        if ($format === 'online') {
            $loc = '';
            $lat = 0.0;
            $lng = 0.0;
        } else if (empty($loc)) {
            trigger_error('EVENT_LOCATION_REQUIRED');
        }

        $limit_enabled = $this->request->is_set_post('limit_registrations_enabled');
        $limit_val = $this->request->variable('event_attendees_limit', 0);
        $max_participants = $limit_enabled ? $limit_val : 0;

        $public_cal = $this->request->is_set_post('public_calendar_enabled');
        $visibility = $public_cal ? 0 : 1;
        $access_token = '';

        if ($visibility === 1) {
            $access_token = $event ? $this->event_access->ensure_private_token($event) : $this->event_access->generate_private_token();
        }

        return [$subject, $start, $end, $loc, $desc, $cat_id, $lat, $lng, $visibility, $access_token, $max_participants];
    }

    protected function prepare_description(&$desc, &$uid, &$bitfield, &$options)
    {
        $allow_bbcode = $this->user->optionget('bbcode') ? true : false;
        $allow_urls = $this->user->optionget('viewimg') ? true : false;
        $allow_smilies = $this->user->optionget('smilies') ? true : false;

        if ($this->request->is_set_post('disable_bbcode')) {
            $allow_bbcode = false;
        }
        if ($this->request->is_set_post('disable_smilies')) {
            $allow_smilies = false;
        }
        if ($this->request->is_set_post('disable_magic_url')) {
            $allow_urls = false;
        }

        generate_text_for_storage($desc, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);
    }
}
