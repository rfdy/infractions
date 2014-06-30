<?php

namespace rfd\infractions\mcp;

class mcp_main_info
{
    function module()
    {
        return array(
            'filename'	=> '\rfd\infractions\mcp\mcp_main_module',
            'title'		=> 'Infractions',
            'version'	=> '0.0.1',
            'modes'		=> array(
                'front_page' => array(
                                    'title' => 'Front page',
                                    'auth'  => 'acl_m_infractions_add',
                                    'cat'   => array('Infractions')
                                ),
                'list'	=> array(
                                    'title' => 'List infractions',
                                    'auth'  => 'acl_m_infractions_add',
                                    'cat'   => array('Infractions')
                            ),
                'issue' => array(
                                    'title' => 'Issue infraction to user',
                                    'auth' => 'acl_m_infractions_add',
                                    'cat' => array('Infractions')
                    ),
                'view_user' => array(
                                    'title' => 'View infractions issued to a user',
                                    'auth' => 'acl_m_infractions_add',
                                    'cat' => array('Infractions'),
                ),
            ),
        );
    }
}