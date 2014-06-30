<?php

/**
 * @ignore
 */

namespace rfd\infractions\ucp;

if (!defined('IN_PHPBB'))
{
    exit;
}

/**
 * ucp_profile
 * Changing profile settings
 *
 * @todo what about pertaining user_sig_options?
 * @package ucp
 */
class ucp_main_module
{
    var $u_action;

    /**
     * @var $user_infraction_manager \rfd\infractions\Service\UserInfractionsManager
     */
    private $user_infraction_manager;

    public function setUserInfractionManager(\rfd\infractions\Service\UserInfractionsManager $user_infraction_manager) {
        $this->user_infraction_manager = $user_infraction_manager;
    }

    function main($id, $mode)
    {
        global $db, $user, $template, $phpbb_container, $phpbb_root_path, $phpEx;

        $this->user_infraction_manager = $phpbb_container->get('infractions.user_infractions_manager');

        switch ($mode)
        {
            case 'infraction_list':
                $this->tpl_name = 'ucp_infraction_list';
                $this->page_title = "Infraction List";

                $user_id = $user->data['user_id'];
                $infractions = $this->user_infraction_manager->get_user_infractions($user_id, 0, 0, true);

                foreach ($infractions as $key => $infraction)
                {
                    $result = $db->sql_query('SELECT * FROM ' . USERS_TABLE . ' WHERE user_id=' . $infraction['moderator_id']);
                    $mod_row = $db->sql_fetchrow($result);

                    $infraction['moderator_username'] = get_username_string('full', $mod_row['user_id'], $mod_row['username'], $mod_row['user_colour']);
                    $infractions[$key] = $infraction;
                }

                if (!function_exists('get_user_rank'))
                {
                    include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
                }
                get_user_rank($user->data['user_rank'], $user->data['user_posts'], $rank_title, $rank_img, $rank_img_src);
                $avatar_img = phpbb_get_user_avatar($user->data);

                $template->assign_vars(array(
                    'JOINED'			=> $user->format_date($user->data['user_regdate']),
                    'POSTS'				=> ($user->data['user_posts']) ? $user->data['user_posts'] : 0,
                    'INFRACTION_POINTS'	=> ($user->data['user_infraction_points']) ? $user->data['user_infraction_points'] : 0,
                    'USERNAME_FULL'		=> get_username_string('full', $user->data['user_id'], $user->data['username'], $user->data['user_colour']),

                    'AVATAR_IMG'		=> $avatar_img,
                    'RANK_IMG'			=> $rank_img,
                    'USER_INFRACTIONS'       => $infractions,
                    'USERNAME'          => $user->data['username'],
                    'INFRACTION_POINTS' => $user->data['user_infraction_points'],
                ));
            break;
        }

    }
}
