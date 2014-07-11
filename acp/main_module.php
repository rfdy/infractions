<?php

namespace rfd\infractions\acp;

use rfd\infractions\Service\InfractionRulesManager;

class main_module
{
    public $u_action;
    public $options;

    const SMALL_INT_MAX = 50000;
    const MAX_TITLE_LEN = 50;

    private $infraction_manager;
    private $rules_manager;
    private $user_infraction_manager;


    public function setInfractionManager(\rfd\infractions\Service\InfractionManager $infraction_manager) {
        $this->infraction_manager = $infraction_manager;
    }
    public function setInfractionRulesManager(\rfd\infractions\Service\InfractionRulesManager $rules_manager) {
        $this->rules_manager = $rules_manager;
    }
    public function setUserInfractionsManager(\rfd\infractions\Service\UserInfractionsManager $user_infraction_manager) {
        $this->$user_infraction_manager = $$user_infraction_manager;
    }

    function main($id, $mode)
    {

        global $user, $template, $request, $phpbb_container;

        $user->add_lang('acp/common');

        switch($mode) {

            case 'infraction_manager':
                $im = $phpbb_container->get('infractions.infraction_manager');
                $this->infraction_manager($im, $request, $template, $user);
                break;

            case 'rules':
                $rm = $phpbb_container->get('infractions.rules_manager');
                $this->rules_manager($rm, $request, $template, $user);
                break;

            case 'resynchronize_rule_penalties':
                $um = $phpbb_container->get('infractions.user_infractions_manager');
                $this->resynchronize_rule_penalties($um, $request, $template, $user);
                break;

            //TODO: Add a module for this
            /*case 'stats_view':
                $im = $phpbb_container->get('infractions.infraction_manager');
                $um = $phpbb_container->get('infractions.user_infractions_manager');
                $this->stats_view($im, $um, $request, $template, $user);
            break;*/
        }
    }

    /**
     * Handle infractions mode
     *
     * @param $manager
     * @param $request
     * @param $template
     * @param $user
     */
    private function infraction_manager($manager, $request, $template, $user) {
        $titleErr = $pointsErr = $expiresErr = "";
        $action = $request->variable('action', 'list');

        $template->assign_vars(array(
            'U_ACTION'				=> $this->u_action,
        ));

        // -- list the infractions
        if ($action == 'list') {
            $this->tpl_name = 'infraction_list';
            $this->page_title = $user->lang('ACP_INF_MANAGER');
            $infractions = $manager->get_infractions();

            // Generate edit and delete urls for template
            foreach ($infractions as $key => $infraction) {
                $infraction['edit_url'] = $this->u_action . '&amp;action=edit&amp;id=' . $infraction['infraction_id'];
                $infraction['delete_url'] = $this->u_action . '&amp;action=delete&amp;id=' . $infraction['infraction_id'];
                $infractions[$key] = $infraction;
            }

            $template->assign_vars(array(
                'infractions'              => $infractions,
                'U_ADD'                    => $this->u_action . "&amp;action=add",
            ));

            // -- edit view a single infraction
        } else if ($action == "edit") {
            $this->tpl_name = 'infraction_form';
            $this->page_title = $user->lang('ACP_INF_MANAGER');

            $infraction_id = (int) $request->variable('id', '0');
            $infraction = $manager->get_infraction($infraction_id);
            if (!$infraction) {
                trigger_error("Infraction not found!" . adm_back_link($this->u_action), E_USER_WARNING);
            }

            $template->assign_vars(array(
                'FORM_TITLE'    => "Edit Infraction",
                'infraction'    => $infraction,
            ));

            add_form_key('infractions');

            // -- add view a single infraction
        } else if ($action == "add") {
            $this->tpl_name = 'infraction_form';
            $this->page_title = $user->lang('ACP_INF_MANAGER');

            $infraction = array('infraction_id' => 0,
                'title' => '',
                'points' => '',
                'expires_days' => '');

            $template->assign_vars(array(
                'FORM_TITLE'    => "Add Infraction",
                'infraction'    => $infraction,
            ));

            add_form_key('infractions');

            // -- save or create an infraction from POST
        } else if ($action == "save" && $request->is_set_post('submit')) {

            $this->titleErr = $this->pointsErr = $this->expiresErr = "";
            if (!check_form_key('infractions'))
            {
                trigger_error('FORM_INVALID');
            }
            $title = trim($request->variable('title', ''));
            $points = $request->variable('points', 0);
            $expires = $request->variable('expires_days', 0);

            if (empty($title) || strlen($title) > self::MAX_TITLE_LEN) {
                $titleErr = "Missing or excessively long title.";
            }
            if ($points < 0 || $points > self::SMALL_INT_MAX) {
                $pointsErr = "* Points not within range (0-".self::SMALL_INT_MAX.")";
            }
            if ($expires < 0 || $expires > self::SMALL_INT_MAX) {
                $expiresErr = "* Number of days for expiry not within range (0-".self::SMALL_INT_MAX.")";
            }

            if (!$titleErr && !$pointsErr && !$expiresErr) {
                $id = $request->variable('infraction_id', 0);
                if ($id) {
                    $manager->edit_infraction($id, $title, $points, $expires);
                }
                else {
                    $manager->add_infraction($title, $points, $expires);
                }
                trigger_error($user->lang('ACP_INFRACTION_SAVED') . adm_back_link($this->u_action));
            } else {
                $this->tpl_name = 'infraction_form';
                $this->title = 'Infraction Form';
                $template->assign_vars(array(
                    'infraction'            => array('title' => $title, 'points' => $points, 'expires_days' => $expires),
                    'TITLE_ERR'              => $titleErr,
                    'POINTS_ERR'              => $pointsErr,
                    'EXPIRES_ERR'              => $expiresErr,
                ));
            }
            // -- Deletes an infraction type
        } else if ($action == 'delete') {
            $id = $request->variable('id', 0);

            confirm_box(false, $user->lang['CONFIRM_OPERATION']);

            if (confirm_box(true))
            {
                $manager->delete_infraction($id);

                if ($request->is_ajax())
                {
                    $json_response = new \phpbb\json_response;
                    $json_response->send(array(
                        'MESSAGE_TITLE'	=> $user->lang['INFORMATION'],
                        'MESSAGE_TEXT'	=> 'The Infraction was successfully deleted',
                        'REFRESH_DATA'	=> array(
                            'time'	=> 3
                        )
                    ));
                }
            }
        } else {
            trigger_error("Unknown Action!" . adm_back_link($this->u_action));
        }
    }

    /**
     * Handle infraction rules mode
     *
     * @param InfractionRulesManager           $manager
     * @param \phpbb\request\request_interface $request
     * @param                                  $template
     * @param                                  $user
     */
    private function rules_manager(InfractionRulesManager $manager, \phpbb\request\request_interface $request, $template, $user) {
        $action = $request->variable('action', 'list');

        $template->assign_vars(array(
            'U_ACTION'	=> $this->u_action,
        ));
        $this->page_title = $user->lang('ACP_INF_RULES');

        // -- list current rules
        if ($action == 'list') {
            $this->tpl_name = 'rules_list';

            $rules = $manager->getRules();

            foreach ($rules as $key => $rule) {
                $rule['edit_url'] = $this->u_action . '&amp;action=edit&amp;id=' . $rule['point_level'];
                $rule['delete_url'] = $this->u_action . '&amp;action=delete&amp;id=' . $rule['point_level'];
                $rule['group_id'] = $manager->getGroupName($rule['group_id']);
                $rule['group_id'] = (!empty($user->lang['G_' . $rule['group_id']]))? $user->lang['G_' . $rule['group_id']] : $rule['group_id'];
                $rules[$key] = $rule;
            }

            $template->assign_vars(array(
                'RULES' => $rules,
                'U_ADD' => $this->u_action . '&amp;action=add',
            ));

            // -- add a new rule form
        } else if ($action == 'add'){
            $this->tpl_name = 'infraction_rules';

            $groups = $manager->getGroups(false);

            if (empty($groups)) {
                trigger_error($user->lang('ACL_NO_GROUPS') . adm_back_link($this->u_action), E_USER_WARNING);
            }

            foreach ($groups as $key => $group) {
                $groups[$key]['group_name'] = (!empty($user->lang['G_' . $group['group_name']]))? $user->lang['G_' . $group['group_name']] : $group['group_name'];
            }

            usort($groups, function($a, $b) {
                return strcasecmp($a['group_name'], $b['group_name']);
            });

            $template->assign_vars(array(
                'FORM_TITLE' => 'Add Infraction Rule',
                'GROUPS'     => $groups,
            ));
            add_form_key('infractions');

            // -- edit a rule form
        } else if ($action == 'edit') {
            $this->tpl_name = 'infraction_rules';

            $groups = $manager->getGroups();

            if (empty($groups)) {
                trigger_error($user->lang('ACL_NO_GROUPS') . adm_back_link($this->u_action), E_USER_WARNING);
            }

            foreach ($groups as $key => $group) {
                $groups[$key]['group_name'] = (!empty($user->lang['G_' . $group['group_name']]))? $user->lang['G_' . $group['group_name']] : $group['group_name'];
            }

            usort($groups, function($a, $b) {
                return strcasecmp($a['group_name'], $b['group_name']);
            });

            $template->assign_vars(array(
                'GROUPS' => $groups,
                'FORM_TITLE' => "Edit Infraction Rule",
                'DEFAULT_RULE' => $manager->getRule($request->variable('id', 0)),
            ));
            add_form_key('infractions');

            // -- delete a rule
        } else if ($action == 'delete') {
            $id = $request->variable('id', 0);

            confirm_box(false, $user->lang['CONFIRM_OPERATION']);

            if (confirm_box(true))
            {
                $manager->delete_rule($id);

                if ($request->is_ajax())
                {
                    $json_response = new \phpbb\json_response;
                    $json_response->send(array(
                        'MESSAGE_TITLE'	=> $user->lang['INFORMATION'],
                        'MESSAGE_TEXT'	=> 'The rule was successfully deleted',
                        'REFRESH_DATA'	=> array(
                            'time'	=> 3
                        )
                    ));
                }
            }

        } else if ($action == 'save' && $request->is_set_post('submit')) {
            $pointsErr = '';

            if (!check_form_key('infractions'))
            {
                trigger_error('FORM_INVALID');
            }

            $id         = $request->variable('id', 0);
            $points     = $request->variable('points', 0);
            $group      = $request->variable('group', 0);
            $use_rank   = $request->variable('use_rank', 0);
            $error = 'ACP_SAVE_DUPLICATE';
            $error_type = E_USER_WARNING;

            if ($points <= 0 || $points > self::SMALL_INT_MAX) {
                $pointsErr = "Points threshold not within range (1-".self::SMALL_INT_MAX.")";
            }

            if (!$pointsErr) {
                if ($id && ($id == $points | $manager->edit_rule($id, $points, $group, $use_rank))) {
                    $error      = 'ACP_RULE_SAVED';
                    $error_type = E_USER_NOTICE;
                } else if ($manager->add_rule($points, $group, $use_rank))  {
                    $error      = 'ACP_RULE_SAVED';
                    $error_type = E_USER_NOTICE;
                }
                trigger_error($user->lang($error) . adm_back_link($this->u_action), $error_type);

            } else {
                $this->tpl_name = 'infraction_rules';

                $groups = $manager->getGroups();

                foreach ($groups as $key => $group) {
                    $groups[$key]['group_name'] = (!empty($user->lang['G_' . $group['group_name']]))? $user->lang['G_' . $group['group_name']] : $group['group_name'];
                }

                usort($groups, function($a, $b) {
                    return strcasecmp($a['group_name'], $b['group_name']);
                });

                $template->assign_vars(array(
                    'POINTS_ERR'              => $pointsErr,
                    'GROUPS'                  => $groups,
                    'DEFAULT_RULE'            => $manager->getRule($request->variable('id', 0)),
                ));
            }
        } else {
            trigger_error("Unknown Action!" . adm_back_link($this->u_action));
        }
    }

    private function resynchronize_rule_penalties($manager, \phpbb\request\request_interface $request, $template, $user) {
        $action = $request->variable('action', 'front');

        if ($action == 'front') {
            $template->assign_vars(array(
                'U_ACTION'				=> $this->u_action,
                'U_RESYNCHRONIZE'         => $this->u_action . '&amp;action=resynchronize',
            ));
            $this->tpl_name = 'resynchronize_rule_front';
            $this->page_title = $user->lang('Resynchronize Rule Penalties');
        } else if ($action == 'resynchronize') {
            $manager->resynchronize();
            // Tell the user that everything went okay
            trigger_error("Rule Penalties have been resynchronize successfully!" . adm_back_link($this->u_action));
        } else {
            trigger_error("Unknown Action!" . adm_back_link($this->u_action));
        }
    }
    // -- Sata
    private function stats_view(\rfd\infractions\Service\InfractionManager $im, \rfd\infractions\Service\UserInfractionsManager $uim, \phpbb\request\request_interface $request, $template, $user) {
        $action = $request->variable('action', 'front');

        if ($action == 'front') {
            $template->assign_vars(array(
                'U_ACTION'				=> $this->u_action,
            ));

            $this->tpl_name = 'infraction_stats';
            $this->page_title = $user->lang('ACP_INF_STATS');

            $infs = $im->get_infractions();

            $usr_infs = $uim->get_infraction_id_counts();

            $summary = array();
            foreach ($infs as $inf) {
                $summary[$inf['infraction_id']] = array("title" => $inf['title'], "count" => 0);
            }

            foreach ($usr_infs as $usr_inf) {
                if ($summary[$usr_inf['infraction_id']]) {
                    $summary[$usr_inf['infraction_id']]['count'] = $usr_inf['cnt'];
                } else {
                    // infraction was deleted
                    $type = ($usr_inf['infraction_id'] == 0) ? '[custom]' : '[removed]';
                    $summary[$usr_inf['infraction_id']] = array("title" => $usr_inf['title'] . " $type", "count" => $usr_inf['cnt']);
                }
            }

            $count = array();
            foreach ($summary as $key => $row) {
                $count[$key] = $row['count'];
            }
            array_multisort($count, SORT_DESC, $summary);

            $template->assign_vars(array(
                'infractions'				=> $summary,
            ));
        } else {
            triger_error("Unknown Action!" . adm_back_link($this->u_action));
        }
    }
}