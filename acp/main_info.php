<?php

namespace rfd\infractions\acp;

class main_info
{
    function module()
    {
        return array(
            'filename'	=> '\rfd\infractions\acp\main_module',
            'title'		=> 'Infractions System',
            'version'	=> '0.0.1',
            'modes'		=> array(
                'infraction_manager' => array(
                                            'title' => 'ACP_INF_MANAGER',
                                            'auth'  => 'acl_a_infractions && acl_a_board',
                                            'cat'   => array('ACP_INF_MANAGER')
                                        ),
                'rules'	=> array(
                            'title' => 'Rules',
                            'auth'  => 'acl_a_infractions && acl_a_board',
                            'cat'   => array('ACP_INF_MANAGER')
                        ),

                'recalculate_rule_penalties'  => array(
                                'title' => 'Recalculate Rule Penalties',
                                'auth' => 'acl_a_infractions && acl_a_board',
                                'cat' => array('ACP_INF_MANAGER')
                        ),

            ),
        );
    }
}