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

class event_manager
{
    /** @var \phpbb\db\driver\driver_interface */
    protected $db;

    /** @var \vinny\calendar\service\map_image */
    protected $map_image;

    /** @var string */
    protected $table_prefix;

    public function __construct(\phpbb\db\driver\driver_interface $db, \vinny\calendar\service\map_image $map_image, $table_prefix)
    {
        $this->db = $db;
        $this->map_image = $map_image;
        $this->table_prefix = $table_prefix;
    }

    public function create_event(array $data)
    {
        $sql = 'INSERT INTO ' . $this->table_prefix . 'eventboard_events ' . $this->db->sql_build_array('INSERT', [
            'user_id' => (int) $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'start_at' => (int) $data['start_at'],
            'end_at' => (int) $data['end_at'],
            'location' => $data['location'],
            'lat' => (float) $data['lat'],
            'lng' => (float) $data['lng'],
            'cat_id' => (int) $data['cat_id'],
            'max_participants' => (int) $data['max_participants'],
            'created_at' => isset($data['created_at']) ? (int) $data['created_at'] : time(),
            'visibility' => (int) $data['visibility'],
            'access_token' => $data['access_token'],
            'desc_uid' => $data['desc_uid'],
            'desc_bitfield' => $data['desc_bitfield'],
            'desc_options' => (int) $data['desc_options'],
        ]);
        $this->db->sql_query($sql);

        $event_id = (int) $this->db->sql_nextid();
        $map_image = $this->map_image->generate($event_id, $data['lat'], $data['lng']);

        if ($map_image !== '') {
            $sql = 'UPDATE ' . $this->table_prefix . "eventboard_events
                SET map_image = '" . $this->db->sql_escape($map_image) . "'
                WHERE event_id = " . $event_id;
            $this->db->sql_query($sql);
        }

        return [
            'event_id' => $event_id,
            'visibility' => (int) $data['visibility'],
            'access_token' => $data['access_token'],
        ];
    }

    public function update_event($event_id, array $data)
    {
        $map_image = $data['map_image'];

        if ($map_image === '' && (float) $data['lat'] != 0.0 && (float) $data['lng'] != 0.0) {
            $map_image = $this->map_image->generate($event_id, $data['lat'], $data['lng']);
        }

        $sql = 'UPDATE ' . $this->table_prefix . 'eventboard_events
            SET ' . $this->db->sql_build_array('UPDATE', [
                'title' => $data['title'],
                'description' => $data['description'],
                'start_at' => (int) $data['start_at'],
                'end_at' => (int) $data['end_at'],
                'location' => $data['location'],
                'lat' => (float) $data['lat'],
                'lng' => (float) $data['lng'],
                'cat_id' => (int) $data['cat_id'],
                'max_participants' => (int) $data['max_participants'],
                'visibility' => (int) $data['visibility'],
                'access_token' => $data['access_token'],
                'desc_uid' => $data['desc_uid'],
                'desc_bitfield' => $data['desc_bitfield'],
                'desc_options' => (int) $data['desc_options'],
                'map_image' => $map_image,
            ]) . '
            WHERE event_id = ' . (int) $event_id;
        $this->db->sql_query($sql);

        return [
            'event_id' => (int) $event_id,
            'visibility' => (int) $data['visibility'],
            'access_token' => $data['access_token'],
        ];
    }

    public function delete_event($event_id)
    {
        $sql = 'DELETE FROM ' . $this->table_prefix . 'eventboard_events
            WHERE event_id = ' . (int) $event_id;
        $this->db->sql_query($sql);

        $sql = 'DELETE FROM ' . $this->table_prefix . 'eventboard_participants
            WHERE event_id = ' . (int) $event_id;
        $this->db->sql_query($sql);

        $sql = 'DELETE FROM ' . $this->table_prefix . 'eventboard_comments
            WHERE event_id = ' . (int) $event_id;
        $this->db->sql_query($sql);
    }
}
