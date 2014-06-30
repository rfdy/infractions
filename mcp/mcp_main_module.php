<?php

namespace rfd\infractions\mcp;

use phpbb\datetime;

class mcp_main_module
{
    public $u_action;
    public $options;


    private $id;

    /** @var  \p_master */
    private $p_master;
	
    const SMALL_INT_MAX = 50000;
    const MAX_TITLE_LEN = 50;
    const MAX_REASON_LEN = 1500;
    const NUM_PER_PAGE = 10;

    function __construct(\p_master $p_master)
    {
        $this->p_master = $p_master;
    }

    /**
     * @var $infraction_manager \rfd\infractions\Service\InfractionManager
     */
    private $infraction_manager;

    /**
     * @var $user_infraction_manager \rfd\infractions\Service\UserInfractionsManager
     */
    private $user_infraction_manager;

    /**
     * @var $rules_manager \rfd\infractions\Service\InfractionRulesManager
     */
    private $rules_manager;

    public function setInfractionManager(\rfd\infractions\Service\InfractionManager $infraction_manager) {
        $this->infraction_manager = $infraction_manager;
    }
    public function setInfractionRulesManager(\rfd\infractions\Service\InfractionRulesManager $rules_manager) {
        $this->rules_manager = $rules_manager;
    }
    public function setUserInfractionManager(\rfd\infractions\Service\UserInfractionsManager $user_infraction_manager) {
        $this->user_infraction_manager = $user_infraction_manager;
    }

    public function fetch_user_row($db, $user_id) {
        $sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE user_id=' . $user_id;
        $result = $db->sql_query($sql);
        $user_row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        return $user_row;
    }

    function main($id, $mode)
    {
        global $db, $user, $auth, $template, $cache, $request;
        global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
        global $phpbb_container;

        global $module;

        $this->id = $id;

        // user infraction manager
        $this->user_infraction_manager = $phpbb_container->get('infractions.user_infractions_manager');

        // infraction manager
        $this->infraction_manager = $phpbb_container->get('infractions.infraction_manager');

        //User Infraction Manager
        $this->user_infraction_manager = $phpbb_container->get('infractions.user_infractions_manager');

        add_form_key('mcp_infractions');

        switch($mode) {

            case 'front_page':
                $module->set_display($id, 'issue', false);
                $module->set_display($id, 'view_user', false);
                $this->front_page();
            break;

            case 'list':
                $module->set_display($id, 'issue', false);
                $module->set_display($id, 'view_user', false);
                $this->render_list();
            break;

            case 'issue':
                $module->set_display($id, 'view_user', false);
                $this->issue_infraction_view();
            break;

            case 'view_user':
                $module->set_display($id, 'issue', false);
                $this->view_user_infractions();
            break;

        }
    }

    private function front_page() {
        global $phpEx, $phpbb_root_path, $config;
        global $template, $db, $user, $auth;

        $infractions = empty($infractions) ? "No Infractions have been created." : $infractions;

        $all_recent_issued = $this->user_infraction_manager->get_users_infractions(0, 10);
        $mod_recent_issued = $this->user_infraction_manager->get_infractions_by_moderator($user->data['user_id'], 5);

        foreach ($all_recent_issued as $key => $infraction)
        {
            $mod_row = $this->fetch_user_row($db, $infraction['moderator_id']);
            $user_row = $this->fetch_user_row($db, $infraction['user_id']);

            $infraction['username'] = get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']);
            $infraction['moderator_username'] = get_username_string('full', $mod_row['user_id'], $mod_row['username'], $mod_row['user_colour']);
            $infraction['view_infractions_link'] = append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=' .$this->id.'&amp;mode=view_user&amp;u=' . $user_row['user_id']);
            $all_recent_issued[$key] = $infraction;
        }
        foreach ($mod_recent_issued as $key => $mod_infraction)
        {
            $user_row = $this->fetch_user_row($db, $mod_infraction['user_id']);

            $mod_infraction['username'] = get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']);
            $mod_infraction['moderator_username'] = get_username_string('full', $user->data['user_id'], $user->data['username'], $user->data['user_colour']);
            $mod_infraction['view_infractions_link'] = append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=' .$this->id.'&amp;mode=view_user&amp;u=' . $user_row['user_id']);
            $mod_recent_issued[$key] = $mod_infraction;
        }

        $template->assign_vars(array(
            'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'),
            'U_POST_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=' .$this->id.'&amp;mode=issue'),
            'U_ACTION'			=> $this->u_action,
            'INFRACTIONS'       => $infractions,
            'ALL_ISSUED'        => $all_recent_issued,
            'MOD_ISSUED'        => $mod_recent_issued,
        ));
        $this->tpl_name = 'mcp_infractions_front';
        $this->page_title = "Give Infraction";

    }

    private function render_list() {
        global $phpEx, $phpbb_root_path, $config, $request, $phpbb_container;
        global $template, $db, $user, $auth;

        $action = $request->variable('action', 'list');
        $template->assign_vars(array(
            'U_ACTION'				=> $this->u_action,
        ));

        $this->tpl_name = 'mcp_infractions_list';
        $this->page_title = "Infractions List";

        if ($action == 'list') {
            /** @var \phpbb\pagination $pagination */
            $pagination = $phpbb_container->get('pagination');
            $infractions_count = $this->user_infraction_manager->get_users_infractions_count();
            $start	= $request->variable('start', 0);
            if ($start > $infractions_count) {
                $start = 0;
            }

            $search_username = $request->variable('username', '');
            $search_user_id = $request->variable('u', '');

            $search_user_row = false;
            if ($search_user_id || $search_username) {
                $sql_where = ($search_user_id) ? "user_id = $search_user_id" : "username_clean = '" . $db->sql_escape(utf8_clean_string($search_username)) . "'";

                $sql = 'SELECT *
			    FROM ' . USERS_TABLE . '
			    WHERE ' . $sql_where;
                $result = $db->sql_query($sql);
                $search_user_row = $db->sql_fetchrow($result);
                $db->sql_freeresult($result);

                if (!search_user_row)
                {
                    trigger_error('NO_USER');
                }

            }

            $user_infractions = $this->user_infraction_manager->get_users_infractions($start, self::NUM_PER_PAGE);

            // generate timezones, a url to cancel the infraction, and usernames
            if ($user->data['user_timezone']) {
                $timezone = new \DateTimeZone($user->data['user_timezone']);

            } else {
                $timezone = new \DateTimeZone($config['board_timezone']);
            }
            foreach ($user_infractions as $key => $user_infraction) {
                $user_infraction['cancel_url'] = $this->u_action . '&amp;action=cancel&amp;id=' . $user_infraction['id'];

                $user_row = $this->fetch_user_row($db, $user_infraction['user_id']);
                $user_infraction['username'] = get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']);
                $user_infraction['view_infractions_link'] = append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=' .$this->id.'&amp;mode=view_user&amp;u=' . $user_row['user_id']);

                $user_row = $this->fetch_user_row($db, $user_infraction['moderator_id']);
                $user_infraction['moderator_username'] = get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']);

                $user_infraction['date_created'] = new \DateTime('@' . $user_infraction['date_created']);
                $user_infraction['date_created']->setTimezone($timezone);

                $user_infractions[$key] = $user_infraction;
            }

            $template->assign_vars(array(
                'U_POST_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=' .$this->id.'&amp;mode=view_user'),
                'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'),
                'SEARCH_USERNAME'              => $search_user_row ? $search_user_row['username'] : '',
                'USER_INFRACTIONS'           => $user_infractions,

                'S_CAN_REMOVE'      => $auth->acl_get('m_infractions_remove'),
                'TOTAL_INFRACTIONS' => $infractions_count,
            ));

            $base_url = append_sid("{$phpbb_root_path}mcp.$phpEx", "i=" . $this->id . "&amp;mode=list");
            $pagination->generate_template_pagination($base_url, 'pagination', 'start', $infractions_count, self::NUM_PER_PAGE, $start);

        } else if ($action == 'cancel' && $auth->acl_get('m_infractions_remove')) {
            $this->cancel_infraction_ajax($request, $user);
        } else {
            trigger_error("Unknown Action!");
        }
    }

    function issue_infraction_view()
    {
        global $request;
        global $phpEx, $phpbb_root_path, $config;
        global $template, $db, $user;

        $user_id = $request->variable('u', 0);
        $post_id = $request->variable('postid', 0);
        $username = $request->variable('username', '', true);
        $never_expire = $request->variable('never_expire', false);
        $give_warning = $request->variable('give_warning', false);

        $moderator_id = $user->data['user_id'];
        $title = $request->variable('title', '');
        $reason = $request->variable('reason', '');
        $infraction_id = $request->variable('infraction', 0);
        $points = $request->variable('points', 0);
        $expire_days = $request->variable('expires_days', 0);
        $action = $request->variable('action', '');

        $err = array();

        if ($action == 'add_infraction' && !$give_warning && ($points < 1 || $points > self::SMALL_INT_MAX)) {
            $err['points'] = true;
            $err['all'] = true;
        }
        if ($action == 'add_infraction' && !$never_expire && !$give_warning && ($expire_days < 1 || $expire_days > self::SMALL_INT_MAX)) {
            $err['expire'] = true;
            $err['all'] = true;
        }
        if ($action == 'add_infraction' && (empty($title) || strlen($title) > self::MAX_TITLE_LEN)) {
            $err['title'] = true;
            $err['all'] = true;
        }
        if ($action == 'add_infraction' && strlen($reason) > self::MAX_REASON_LEN) {
            $err['reason'] = true;
            $err['all'] = true;
        }

        $this->tpl_name = 'mcp_infract_user';

        $sql_where = ($user_id) ? "user_id = $user_id" : "username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";

        $sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE ' . $sql_where;
        $result = $db->sql_query($sql);
        $user_row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        $sql = 'SELECT * FROM ' . POSTS_TABLE . ' WHERE post_id='. $post_id;
        $result = $db->sql_query($sql);
        $post_row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if (!$user_row)
        {
            trigger_error('NO_USER');
        }

        // Prevent someone from issuing infraction to themselves
        if ($user_row['user_id'] == $user->data['user_id'])
        {
            trigger_error('CANNOT_ISSUE_SELF_INFRACTION');
        }

        $user_id = $user_row['user_id'];

        if (strpos($this->u_action, "&amp;postid=$post_id&amp;u=$user_id") === false)
        {
            $this->p_master->adjust_url("&amp;postid=$post_id&amp;u=$user_id");
            $this->u_action .= "&amp;postid=$post_id&amp;u=$user_id";
        }

        // Check if can send a notification

        if ($action == 'add_infraction' && !$err['points'] && !$err['expire'] && !$err['title'] && !$err['reason'])
        {
            if (check_form_key('mcp_infractions'))
            {
                $this->add_infraction($moderator_id, $user_id, $post_id, $title, $reason, $infraction_id, $points, $expire_days);
                $list_url = append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=' .$this->id.'&amp;mode=list');
                trigger_error($user->lang['USER_INFRACTION_ADDED'] . '<br /><br />' . sprintf($user->lang['GO_TO_INFRACTION_LIST'], '<a href="' . $list_url . '">', '</a>'));
            }
            else
            {
                trigger_error($user->lang['FORM_INVALID'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $this->u_action . '">', '</a>'));
            }
        }

        // Generate the appropriate user information for the user we are looking at
        if (!function_exists('get_user_rank'))
        {
            include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
        }
        get_user_rank($user_row['user_rank'], $user_row['user_posts'], $rank_title, $rank_img, $rank_img_src);
        $avatar_img = phpbb_get_user_avatar($user_row);

        //generates recent infractions of the user
        $recent_infractions = $this->user_infraction_manager->get_user_infractions($user_id, 0, 10);
        foreach ($recent_infractions as $key => $infraction)
        {
            $result = $db->sql_query('SELECT * FROM ' . USERS_TABLE . ' WHERE user_id=' . $infraction['moderator_id']);
            $mod_row = $db->sql_fetchrow($result);

            $infraction['created_timestamp'] = date("D M d, Y", $infraction["date_created"]);
            $infraction['moderator_username'] = get_username_string('full', $mod_row['user_id'], $mod_row['username'], $mod_row['user_colour']);
            $recent_infractions[$key] = $infraction;
        }


        //Generates forum post to be inserted into page, if infraction issued to post.
        $post_text = $post_row['post_text'];

        $post_text = generate_text_for_display($post_text, $post_row['bbcode_uid'], $post_row['bbcode_bitfield'], $post_row['enable_bbcode']);
        $post_text = smiley_text($post_text);

        $template->assign_vars(array(
            'U_POST_ACTION'		=> $this->u_action,

            'INFRACTIONS'       => $this->infraction_manager->get_infractions(),

            'RANK_TITLE'		=> $rank_title,
            'JOINED'			=> $user->format_date($user_row['user_regdate']),
            'POSTS'				=> ($user_row['user_posts']) ? $user_row['user_posts'] : 0,
            'INFRACTION_POINTS'			=> ($user_row['user_infraction_points']) ? $user_row['user_infraction_points'] : 0,

            'USER_ID'           => $user_row['user_id'],
            'USERNAME_FULL'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
            'USERNAME_COLOUR'	=> get_username_string('colour', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
            'USERNAME'			=> get_username_string('username', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
            'POST_TEXT'         => $post_text,
            'U_PROFILE'			=> get_username_string('profile', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
            'HAS_INFRACTED'     => $this->user_infraction_manager->post_has_infracted($post_id),
            'RECENT_INFRACTIONS'=> $recent_infractions,

            'INFRACTION_TYPE'   => $infraction_id,
            'TITLE'             => $title,
            'POINTS'            => $points,
            'EXPIRE_DAYS'       => $expire_days,
            'NEVER_EXPIRE'      => $never_expire,
            'GIVE_WARNING'      => $give_warning,
            'REASON'            => $reason,

            'AVATAR_IMG'		=> $avatar_img,
            'RANK_IMG'			=> $rank_img,
            'MAX_INT'           => self::SMALL_INT_MAX,
            'ERROR'             => $err,
        ));

        return $user_id;
    }

    private function view_user_infractions() {
        global $phpEx, $phpbb_root_path, $request, $phpbb_container;
        global $template, $db, $user, $auth, $config;

        $this->tpl_name = 'mcp_infractions_user_view';
        $this->page_title = "View Infraction for User";

        $action = $request->variable('action', 'list');

        if ($action == 'list') {
            /** @var \phpbb\pagination $pagination */
            $pagination = $phpbb_container->get('pagination');
            $infractions_count = $this->user_infraction_manager->get_users_infractions_count();
            $start	= $request->variable('start', 0);
            if ($start > $infractions_count) {
                $start = 0;
            }

            $search_username = $request->variable('username', '');
            $search_user_id = $request->variable('u', '');

            $sql_where = ($search_user_id) ? "user_id = $search_user_id" : "username_clean = '" . $db->sql_escape(utf8_clean_string($search_username)) . "'";

            $sql = 'SELECT *
                FROM ' . USERS_TABLE . '
                WHERE ' . $sql_where;
            $result = $db->sql_query($sql);
            $user_row = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);

            if (!$user_row)
            {
                trigger_error('NO_USER');
            }
            $user_id = $user_row['user_id'];

            if (strpos($this->u_action, "&amp;u=$user_id") === false)
            {
                $this->p_master->adjust_url('&amp;u=' . $user_id);
                $this->u_action .= "&amp;u=$user_id";
            }

            if ($auth->acl_get('m_infractions_add')) {
                $template->assign_vars(array(
                    'U_ISSUE'	=>  append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=' .$this->id.'&amp;mode=issue&amp;u=' . $user_id),
                ));
            }
            // Generate the appropriate user information for the user we are looking at
            if (!function_exists('get_user_rank'))
            {
                include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
            }
            get_user_rank($user_row['user_rank'], $user_row['user_posts'], $rank_title, $rank_img, $rank_img_src);
            $avatar_img = phpbb_get_user_avatar($user_row);

            $user_infractions = $this->user_infraction_manager->get_users_infractions($start, self::NUM_PER_PAGE, $user_id);

            // generate dates, a url to cancel the infraction, and usernames
            if ($user->data['user_timezone']) {
                $timezone = new \DateTimeZone($user->data['user_timezone']);

            } else {
                $timezone = new \DateTimeZone($config['board_timezone']);
            }
            foreach ($user_infractions as $key => $user_infraction) {
                $user_infraction['cancel_url'] = $this->u_action . '&amp;action=cancel&amp;id=' . $user_infraction['id'];

                $mod_row = $this->fetch_user_row($db, $user_infraction['moderator_id']);
                $user_infraction['moderator_username'] = get_username_string('full', $mod_row['user_id'], $mod_row['username'], $mod_row['user_colour']);

                $user_infraction['date_created'] = new \DateTime('@' . $user_infraction['date_created']);
                $user_infraction['date_created']->setTimezone($timezone);

                $user_infractions[$key] = $user_infraction;
            }

            $infractions_count = $this->user_infraction_manager->get_users_infractions_count($user_id);

            $template->assign_vars(array(
                'U_POST_ACTION'		=> $this->u_action,

                'RANK_TITLE'		=> $rank_title,
                'JOINED'			=> $user->format_date($user_row['user_regdate']),
                'POSTS'				=> ($user_row['user_posts']) ? $user_row['user_posts'] : 0,
                'INFRACTION_POINTS'			=> ($user_row['user_infraction_points']) ? $user_row['user_infraction_points'] : 0,

                'USER_ID'           => $user_row['user_id'],
                'USERNAME_FULL'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
                'USERNAME_COLOUR'	=> get_username_string('colour', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
                'USERNAME'			=> get_username_string('username', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
                'U_PROFILE'			=> get_username_string('profile', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

                'AVATAR_IMG'		=> $avatar_img,
                'RANK_IMG'			=> $rank_img,

                'USER_INFRACTIONS'  => $user_infractions,
                'TOTAL_INFRACTIONS' => $infractions_count,
                'S_CAN_REMOVE'      => $auth->acl_get('m_infractions_remove'),
            ));

            $base_url = append_sid("{$phpbb_root_path}mcp.$phpEx", "i=" . $this->id . "&amp;mode=view_user&amp;u=" . $user_id);
            $pagination->generate_template_pagination($base_url, 'pagination', 'start', $infractions_count, self::NUM_PER_PAGE, $start);
        } else if ($action == 'cancel' && $auth->acl_get('m_infractions_remove')) {
            $this->cancel_infraction_ajax($request, $user);
        } else {
            trigger_error("Unknown Action!");
        }
    }

    private function cancel_infraction_ajax($request, $user) {
        $id = $request->variable('id', 0);

        confirm_box(false, "Are you sure you wish to cancel this infraction?");

        if (confirm_box(true))
        {
            $cancel = $this->user_infraction_manager->cancel_user_infraction($id);
            add_log('mod', $cancel['forum_id'], $cancel['topic_id'], 'LOG_CANCELLED_INFRACTION');


            if ($request->is_ajax())
            {
                $json_response = new \phpbb\json_response;
                $json_response->send(array(
                    'MESSAGE_TITLE'	=> $user->lang['INFORMATION'],
                    'MESSAGE_TEXT'	=> 'The infraction was successfully cancelled.',
                    'REFRESH_DATA'	=> array(
                        'time'	=> 3
                    )
                ));
            }
        }
    }

    function add_infraction($moderator_id, $user_id, $post_id, $title, $reason, $infraction_id, $points, $expire_days){
        global $db, $user, $phpbb_root_path, $phpEx, $request;

        if (!class_exists('bbcode')) {
            include __DIR__ . '/../../../../includes/bbcode.php';
        }
        if (!class_exists('message_parser')) {
            include __DIR__ . '/../../../../includes/message_parser.php';
        }
        if (!function_exists('submit_pm')) {
            include __DIR__ . '/../../../../includes/functions_privmsgs.php';
        }

        $sql = 'SELECT * FROM ' . POSTS_TABLE . ' WHERE post_id='. $post_id;
        $result = $db->sql_query($sql);
        $post_row = $db->sql_fetchrow($result);

        $sql = 'SELECT username FROM ' . USERS_TABLE . ' WHERE user_id=' . $user_id;
        $result = $db->sql_query($sql);
        $user_row = $db->sql_fetchrow($result);

        $post_url = append_sid(generate_board_url()."{$phpbb_root_path}viewtopic.$phpEx",'f='.$post_row['forum_id'].'&amp;t='.$post_row['topic_id'].'&amp;p='.$post_row['post_id'].'#p'.$post_row['post_id']);

        $message_parser = new \parse_message();

        if ($post_id){
            $message_parser->message = sprintf($user->lang['INFRACTION_PM_BODY_POST'], $user_row['username'], $title, $reason, $points, "$post_url", $post_row['post_text']);
        }
        else {
            $message_parser->message = sprintf($user->lang['INFRACTION_PM_BODY_USER'], $user_row['username'], $title, $reason, $points);
        }

        $message_parser->parse(true, true, true, false, false, true, true);

        $pm_data = array(
            'from_user_id'			=> $user->data['user_id'],
            'from_user_ip'			=> $user->ip,
            'from_username'			=> $user->data['username'],
            'enable_sig'			=> false,
            'enable_bbcode'			=> true,
            'enable_smilies'		=> true,
            'enable_urls'			=> false,
            'icon_id'				=> 0,
            'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
            'bbcode_uid'			=> $message_parser->bbcode_uid,
            'message'				=> $post_text = generate_text_for_display($message_parser->message, $post_row['bbcode_uid'], $post_row['bbcode_bitfield'], $post_row['enable_bbcode']),
            'address_list'			=> array('u' => array($user_id => 'to')),
        );

        submit_pm('post', $user->lang['INFRACTION_PM_SUBJECT'], $pm_data, false);
        $this->user_infraction_manager->issue_infraction($moderator_id, $user_id, $post_id, $post_row['topic_id'], $request->server('REQUEST_TIME'), $title, $reason, $infraction_id, $points, $expire_days);

        add_log('mod', $post_row['forum_id'], $post_row['topic_id'], 'LOG_ISSUED_INFRACTION', $user_row['username']);
    }
}

