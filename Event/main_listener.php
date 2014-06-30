<?php


namespace rfd\infractions\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface,
    phpbb\event\data                as phpbbEvent;


    /**
     *
     * Class main_listener
     *
     * @package rfd\infractions\Event
     */
class main_listener implements EventSubscriberInterface {

    /** @var  \phpbb\auth\auth */
    private $auth;

    /** @var  \phpbb\user */
    private $user;

    private $phpbb_root_path, $phpEx;

    static public function getSubscribedEvents()
	{
		return array(
            'core.permissions'						=> 'add_permissions',
			'core.user_setup'						=> 'load_language_on_setup',
            'core.memberlist_prepare_profile_data'  => 'memberlist_prepare_profile_data',
            'core.viewtopic_post_rowset_data'       => 'viewtopic_post_rowset_data',
            'core.viewtopic_modify_post_row'        => 'viewtopic_modify_post_row',
		);
	}

	/**
	* Constructor
	*
	*/
	public function __construct($auth, $phpbb_root_path, $phpEx, $user)
	{
        $this->auth = $auth;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->phpEx = $phpEx;
        $this->user = $user;
	}

    /**
     * Add our custom permissions
     *
     *
     * @param  $event
     */
    public function add_permissions($event)
    {
        $data = $event->get_data();

        // Add a permission category for infractions
        $categories           = $data['categories'];
        $categories['infractions'] = 'ACL_CAT_INFRACTIONS';
        $data['categories']      = $categories;

        // Add permissions for infractions
        $permissions = $data['permissions'];
        $permissions['a_infractions']        = array('lang' => 'ACL_A_INFRACTIONS',         'cat' => 'misc');
        $permissions['m_infractions_add']    = array('lang' => 'ACL_M_INFRACTIONS_ADD',     'cat' => 'infractions');
        $permissions['m_infractions_remove'] = array('lang' => 'ACL_M_INFRACTIONS_REMOVE',  'cat' => 'infractions');

        $data['permissions'] = $permissions;

        $event->set_data($data);
    }

    /**
     * @param $event
     */
    public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'rfd/infractions',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

    /**
     * Event: core.memberlist_prepare_profile_data
     *
     * Send the infraction points, a url for the list and a link to issue an infraction for a particular user
     * to the template for the user's profile
     *
     * @param phpbbEvent $event
     * @return bool
     */
    public function memberlist_prepare_profile_data(phpbbEvent $event) {

        if (!$this->auth->acl_get('m_infractions_add')) {
            return false;
        }
        $data = $event->get_data();

        $infraction_points = $data['data']['user_infraction_points'];
        $list_url = append_sid("{$this->phpbb_root_path}mcp.$this->phpEx", 'i=\rfd\infractions\mcp\mcp_main_module&amp;mode=view_user&amp;u=' . $data['data']['user_id']);
        $issue_url = append_sid("{$this->phpbb_root_path}mcp.$this->phpEx", 'i=\rfd\infractions\mcp\mcp_main_module&amp;mode=issue&amp;u=' . $data['data']['user_id']);

        $data['template_data']['INFRACTION_POINTS'] = $infraction_points;
        $data['template_data']['U_INFRACTION_LIST'] = $list_url;
        $data['template_data']['U_ISSUE_INFRACTION'] = $issue_url;

        $event->set_data($data);
    }

    /**
     * Event: core.viewtopic_post_rowset_data
     *
     * Extract the infraction points and the id of the user viewing the post
     * from the raw DB 'row' result and inject them into 'rowset_data' to be used
     * later when displaying the post (i.e. when core.viewtopic_modify_post_row
     * is fired)
     *
     * @param phpbbEvent $event
     * @return bool
     */
    public function viewtopic_post_rowset_data(phpbbEvent $event) {

        if (!$this->auth->acl_get('m_infractions_add')) {
            return false;
        }
        $data = $event->get_data();

        $data['rowset_data']['user_infraction_points'] = $data['row']['user_infraction_points'];
        $data['rowset_data']['issuer_user_id'] = $this->user->data['user_id'];
        $event->set_data($data);
    }

    /**
     * Event: core.memberlist_prepare_profile_data
     *
     * Send the infraction points and a link to issue an infraction for a particular user
     * to the template for a user's post
     *
     * @param phpbbEvent $event
     * @return bool
     */
    public function viewtopic_modify_post_row(phpbbEvent $event) {
        $data = $event->get_data();

        // Don't show the icon if the viewing user does not have permission
        if (!$this->auth->acl_get('m_infractions_add')) {
            return false;
        }

        $infraction_points = $data['row']['user_infraction_points'];
        $issue_url = append_sid("{$this->phpbb_root_path}mcp.$this->phpEx",
            'i=\rfd\infractions\mcp\mcp_main_module&amp;mode=issue&amp;u=' . $data['row']['user_id'] .
            '&amp;postid=' . $data['row']['post_id']);

        $data['post_row']['INFRACTION_POINTS'] = $infraction_points;

        // Don't show the link to issue an infraction if they are looking at their own post
        if ($data['row']['issuer_user_id'] != $data['row']['user_id']) {
            $data['post_row']['U_ISSUE_INFRACTION'] = $issue_url;
        }

        $event->set_data($data);
    }
}
