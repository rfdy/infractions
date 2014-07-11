<?php

namespace rfd\infractions\migrations;

class release_0_0_1 extends \phpbb\db\migration\migration {
    public function effectively_installed() {
        $sql = 'SELECT config_value
				FROM ' . $this->table_prefix . "config
				WHERE config_name = 'phpbb_infractions_version'";
        $result = $this->db->sql_query($sql);
        $version = $this->db->sql_fetchfield('config_value');
        $this->db->sql_freeresult($result);

        return $version && (version_compare($version, '0.0.1') >= 0);
    }

    static public function depends_on() {
        return array('\phpbb\db\migration\data\v310\dev');
    }

    public function update_schema() {
        $ret = array(
            'add_tables' => array(
                $this->table_prefix . 'infractions'			=> array(
                    'COLUMNS'		=> array(
                        'infraction_id'			=> array('UINT', null, 'auto_increment'),
                        'title'		        	=> array('VCHAR:255', ''),
                        'points'		    	=> array('USINT', 0),
                        'expires_days'          => array('USINT', 0)
                    ),
                    'PRIMARY_KEY'	=> 'infraction_id',
                ),
                $this->table_prefix . 'infraction_rules'		=> array(
                    'COLUMNS'		=> array(
                        'point_level'			=> array('USINT', 0),
                        'group_id'		        => array('UINT', 0),
                        'use_rank'		    => array('BOOL', 0),
                    ),
                    'PRIMARY_KEY'	=> 'point_level'
                ),
                $this->table_prefix . 'infractions_users'		=> array(
                    'COLUMNS'		=> array(
                        'id'                    => array('UINT', null, 'auto_increment'),
                        'moderator_id'			=> array('UINT', 0),
                        'user_id'		        => array('UINT', 0),
                        'post_id'		        => array('UINT', 0),
                        'topic_id'		        => array('UINT', 0),
                        'date_created'		    => array('TIMESTAMP', 0),
                        'title'                 => array('VCHAR:255', ''),
                        'reason'                => array('VCHAR:255', ''),
                        'infraction_id'         => array('UINT', 0),
                        'points'                => array('USINT', 0),
                        'expires_days'          => array('USINT', 0),
                        'status'                => array('TINT:4', 0),
                    ),
                    'PRIMARY_KEY'	=> 'id',
                    'KEYS'           => array(
                        'user_id'               => array('INDEX', 'user_id'),
                    )
                ),
                $this->table_prefix . 'infr_user_rules'		=> array(
                    'COLUMNS'		=> array(
                        'id'                    => array('UINT', null, 'auto_increment'),
                        'user_id'			    => array('UINT', 0),
                        'group_id'		        => array('UINT', 0),
                        'rank'		            => array('TINT:4', 0),
                        'point_level'		    => array('UINT', 0),
                    ),
                    'PRIMARY_KEY'	=> 'id',
                    'KEYS'           => array(
                        'user_id'               => array('INDEX', 'user_id'),
                    )
                ),
            ),
            'add_columns'	=> array(
                $this->table_prefix . 'users'   => array(
                    'user_infraction_points'	        => array('UINT', 0),
                ),
            ),
        );
        return $ret;
    }

    public function revert_schema() {
        return array(
            'drop_tables'	=> array(
                $this->table_prefix . 'infractions',
                $this->table_prefix . 'infraction_rules',
                $this->table_prefix . 'infractions_users',
                $this->table_prefix . 'infr_user_rules',
            ),
            'drop_columns'	=> array(
                $this->table_prefix . 'users' => array(
                    'user_infraction_points',
                ),
            ),
        );
    }

    public function update_data() {
        return array(
            // Config values
            array('config.add', array('phpbb_infractions_version', '0.0.1')),
            array('config.add', array('phpbb_infractions_rules_url', 'http://therules.com')),

            // permissions
			array('permission.add', array('a_infractions', true)),
			array('permission.add', array('m_infractions_add', true)),
			array('permission.add', array('m_infractions_remove', true)),

            // add new permissions to existing roles
            array('permission.permission_set', array('ROLE_ADMIN_FULL',     'a_infractions')),
            array('permission.permission_set', array('ROLE_MOD_STANDARD',   'm_infractions_add')),
            //array('permission.permission_set', array('ROLE_MOD_STANDARD',   'm_infractions_remove')),
            array('permission.permission_set', array('ROLE_MOD_FULL',       'm_infractions_add')),
            array('permission.permission_set', array('ROLE_MOD_FULL',       'm_infractions_remove')),

            // ACP: Infractions section created within the Users & Groups section
            array('module.add', array('acp', 'ACP_CAT_USERGROUP', 'Infractions')),

            // ACP: Infractions manager
            array('module.add', array('acp', 'Infractions', array(
                'module_basename'	=> '\rfd\infractions\acp\main_module',
                'module_langname'	=> 'ACP_INF_MANAGER',
                'module_mode'	=> 'infraction_manager',
                'module_auth'	=> 'acl_a_infractions && acl_a_board',
            ))),

            // ACP: Infractions rules manager
            array('module.add', array('acp', 'Infractions', array(
                'module_basename'	=> '\rfd\infractions\acp\main_module',
                'module_langname'	=> 'Rules',
                'module_mode'	=> 'rules',
                'module_auth'	=> 'acl_a_infractions && acl_a_board',
            ))),
            array('module.add', array('acp', 'Infractions', array(
                'module_basename'	=> '\rfd\infractions\acp\main_module',
                'module_langname'	=> 'Resynchronize Rule Penalties',
                'module_mode'	=> 'resynchronize_rule_penalties',
                'module_auth'	=> 'acl_a_infractions && acl_a_board',
            ))),

            // MCP: Infractions section (ie. the main tab)
            array('module.add', array('mcp', '', 'Infractions')),

            // MCP: Infractions front (landing) page
            array('module.add', array('mcp', 'Infractions', array(
                'module_basename'	=> '\rfd\infractions\mcp\mcp_main_module',
                'module_langname'	=> 'Front page',
                'module_mode'	=> 'front_page',
                'module_auth'	=> 'acl_m_infractions_add',
            ))),

            // MCP: Infractions listing
            array('module.add', array('mcp', 'Infractions', array(
                'module_basename'	=> '\rfd\infractions\mcp\mcp_main_module',
                'module_langname'	=> 'List Infractions',
                'module_mode'	=> 'list',
                'module_auth'	=> 'acl_m_infractions_add',
            ))),

            // MCP: Issue an infraction to someone
            array('module.add', array('mcp', 'Infractions', array(
                'module_basename'	=> '\rfd\infractions\mcp\mcp_main_module',
                'module_langname'	=> 'Issue Infraction',
                'module_mode'	=> 'issue',
                'module_auth'	=> 'acl_m_infractions_add',
            ))),

            // MCP: view a particular user's infractions
            array('module.add', array('mcp', 'Infractions', array(
                'module_basename'	=> '\rfd\infractions\mcp\mcp_main_module',
                'module_langname'	=> 'View User Infractions',
                'module_mode'	=> 'view_user',
                'module_auth'	=> 'acl_m_infractions_add',
            ))),

            // UCP: Infractions section (ie. the main tab)
            array('module.add', array('ucp', '', 'Infractions')),

            // UCP: Infractions list page
            array('module.add', array('ucp', 'Infractions', array(
                'module_basename'	=> '\rfd\infractions\ucp\ucp_main_module',
                'module_langname'	=> 'Infraction List',
                'module_mode'	=> 'infraction_list',
            ))),



            /**
             * The permissions need to end up as:
             * INSERT INTO phpbb_acl_options (auth_option, is_global, is_local, founder_only) VALUES ('u_thanks_give', 1, 0, 0);
             */
         //   array('permission.add', array('u_asdasd', true)),
        );
    }

}
