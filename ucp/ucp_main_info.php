<?php
/**
 *
 * @package ucp
 * @copyright (c) 2005 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rfd\infractions\ucp;

/**
 * @package module_install
 */
class ucp_main_info
{
    function module()
    {
        return array(
            'filename'	=> '\rfd\infractions\ucp\ucp_main_module',
            'title'		=> 'Infraction',
            'version'	=> '0.0.1',
            'modes'		=> array(
                'infraction_list'	=> array('title' => 'Infraction List', 'cat' => array('Infractions')),
            ),
        );
    }

    function install()
    {
    }

    function uninstall()
    {
    }
}
