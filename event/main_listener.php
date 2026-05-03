<?php
/**
 *
 * EventBoard extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 _Vinny_ <https://github.com/vinny>
 * @license All Rights Reserved
 *
 */

namespace vinny\calendar\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\controller\helper */
    protected $helper;

    /** @var \phpbb\template\template */
    protected $template;

    /** @var \phpbb\config\config */
    protected $config;

    /**
     * Constructor
     *
     * @param \phpbb\user $user
     * @param \phpbb\controller\helper $helper
     * @param \phpbb\template\template $template
     * @param \phpbb\config\config $config
     */
    public function __construct(\phpbb\user $user, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\config\config $config)
    {
        $this->user = $user;
        $this->helper = $helper;
        $this->template = $template;
        $this->config = $config;
    }

    static public function getSubscribedEvents()
    {
        return [
            'core.user_setup' => 'load_language_on_setup',
            'core.page_header' => 'add_page_header_link',
            'core.permissions' => 'add_permissions',
        ];
    }

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = [
            'ext_name' => 'vinny/calendar',
            'lang_set' => 'common',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function add_page_header_link($event)
    {
        if (empty($this->config['vinny_calendar_enable'])) {
            return;
        }

        $this->template->assign_vars([
            'U_EVENT_CALENDAR' => $this->helper->route('vinny_calendar_controller'),
        ]);
    }

    public function add_permissions($event)
    {
        $categories = $event['categories'];
        $categories['eventboard'] = 'ACL_CAT_EVENTBOARD';
        $event['categories'] = $categories;

        $permissions = $event['permissions'];
        $permissions['u_eventboard_view'] = ['lang' => 'ACL_U_EVENTBOARD_VIEW', 'cat' => 'eventboard'];
        $permissions['u_eventboard_create'] = ['lang' => 'ACL_U_EVENTBOARD_CREATE', 'cat' => 'eventboard'];
        $permissions['u_eventboard_delete'] = ['lang' => 'ACL_U_EVENTBOARD_DELETE', 'cat' => 'eventboard'];
        $event['permissions'] = $permissions;
    }
}
