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

class event_access
{
    /** @var \phpbb\db\driver\driver_interface */
    protected $db;

    /** @var \phpbb\auth\auth */
    protected $auth;

    /** @var string */
    protected $table_prefix;

    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, $table_prefix)
    {
        $this->db = $db;
        $this->auth = $auth;
        $this->table_prefix = $table_prefix;
    }

    public function normalize_token($token)
    {
        return preg_replace('/[^a-f0-9]/i', '', (string) $token);
    }

    public function is_public(array $event)
    {
        return (int) $event['visibility'] === 0;
    }

    public function generate_private_token()
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            return sha1(uniqid(mt_rand(), true));
        }
    }

    public function ensure_private_token(array $event)
    {
        $token = $this->normalize_token($event['access_token'] ?? '');
        return $token ?: $this->generate_private_token();
    }

    public function is_private_token_valid(array $event, $token)
    {
        if ($this->is_public($event)) {
            return true;
        }

        $event_token = $this->normalize_token($event['access_token'] ?? '');
        $provided_token = $this->normalize_token($token);

        return $event_token !== '' && $provided_token !== '' && hash_equals($event_token, $provided_token);
    }

    public function is_event_participant($event_id, $user_id)
    {
        if (!(int) $user_id) {
            return false;
        }

        $sql = 'SELECT id
            FROM ' . $this->table_prefix . 'eventboard_participants
            WHERE event_id = ' . (int) $event_id . '
                AND user_id = ' . (int) $user_id;
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        return (bool) $row;
    }

    public function can_view_event(array $event, $user_id, $token = '')
    {
        if ($this->is_public($event)) {
            return true;
        }

        if ($this->auth->acl_get('a_') || $this->auth->acl_get('m_')) {
            return true;
        }

        if ((int) $user_id && (int) $event['user_id'] === (int) $user_id) {
            return true;
        }

        if ((int) $user_id && $this->is_event_participant($event['event_id'], $user_id)) {
            return true;
        }

        return $this->is_private_token_valid($event, $token);
    }

    public function build_route_params(array $event, array $params = [], $token = '')
    {
        $params = $params ?: [];

        if (!$this->is_public($event)) {
            $params['t'] = $this->ensure_private_token($event);

            if ($this->is_private_token_valid($event, $token)) {
                $params['t'] = $this->normalize_token($token);
            }
        }

        return $params;
    }
}
