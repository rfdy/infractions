<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

global $config;
$FORUM_NAME_TO_REPLACE_INFRACTIONS=$config['sitename'];
$RULES_URL_TO_REPLACE_INFRACTIONS=$config['phpbb_infractions_rules_url'];

$lang = array_merge($lang, array(
	'ACP_DEMO_GOODBYE'			     => 'Should say goodbye?',
    'ACP_INF_MANAGER'			     => 'Infractions',
    'ACP_INF_RULES'			         => 'Infraction Rules',
    'ACP_INF_STATS'                  => 'Infraction Statistics',
	'ACP_INFRACTION_SAVED'	         => 'Infraction has been saved successfully!',
    'ACP_MANAGE_INFRACTIONS'         => 'Manage Infractions',
    'ACP_MANAGE_RULES'               => 'Manage Infraction Rules',
    'ACP_RULE_MANAGER'		      	 => 'Infraction Rules',
    'ACP_RULE_SAVED'                 => 'Rule has been saved successfully!',
    'ACP_SAVE_DUPLICATE'             => 'Duplicate: Rule already exists',
    'ADD_NEW_INFRACTION'             => 'Add New Infraction',
    'UPDATE_INFRACTIONS_RULES_URL'   => 'Update rules URL',
    'ADD_NEW_RULE'                   => 'Add New Rule',
    'ACL_A_INFRACTIONS'              => 'Can manage infractions and rules',
    'ACL_CAT_INFRACTIONS'            => 'Infractions',
    'ACL_M_INFRACTIONS_ADD'          => 'Can issue an infraction',
    'ACL_M_INFRACTIONS_REMOVE'       => 'Can remove an existing infraction',
    'ACL_NO_GROUPS'                  => 'No custom groups available',
    'MULTIPLE_INFRACTIONS'           => 'Warning: User has already been issued an infraction for the following post:',
    'MISSING_FIELDS'                 => 'Error: Missing or incorrect data in fields.',
    'CANNOT_ISSUE_SELF_INFRACTION'            => 'Cannot issue infraction to self',
    'EXPIRE_INFRACTIONS'             => 'Removed Expired Infractions.',
    'RECALCULATE_RULE_PENALTIES'     => "Recalculate Rule Penalties",
    'USER_INFRACTION_ADDED'          => 'Infraction has been issued successfully.',
    'GO_TO_INFRACTION_LIST'          => '%s View list of all infractions %s',
    'LOG_ISSUED_INFRACTION'	         => '<strong>Issued User Infraction</strong><br />» %s',
    'LOG_CANCELLED_INFRACTION'	     => '<strong>Cancelled User Infraction</strong>',
    'INFRACTION_PM_BODY_USER'        => 'Dear %s, </br>
                                         You have received an infraction at '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums. </br></br>
                                         <strong>Infraction:</strong> %s </br>
                                         <strong>Reason:</strong> %s </br></br>
                                         This infraction is worth %s point(s) and may result in restricted access until it expires. Serious infractions will never expire. </br>
                                         <strong>Your infraction history is only visible to you and the moderation team.</strong></br>
                                         Please review the rules so you can avoid future violations: </br>
                                         '.$RULES_URL_TO_REPLACE_INFRACTIONS.' </br>
                                         Only an administrator can reverse an infraction and it is only done if an infraction was given in error. Private message <strong>admin</strong> if you believe an error has occured. </br>
                                         All the best, </br>
                                         '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums',
    'INFRACTION_PM_BODY_POST'        => 'Dear %s, </br>
                                         You have received an infraction at '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums. </br></br>
                                         <strong>Infraction:</strong> %s </br>
                                         <strong>Reason:</strong> %s </br>
                                         This infraction is worth %s point(s) and may result in restricted access until it expires. Serious infractions will never expire. </br>
                                         <strong>Your infraction history is only visible to you and the moderation team.</strong></br>
                                         Please review the rules so you can avoid future violations: </br>
                                         '.$RULES_URL_TO_REPLACE_INFRACTIONS.' </br>
                                         Only an administrator can reverse an infraction and it is only done if an infraction was given in error. Private message <strong>admin</strong> if you believe an error has occured. </br>
                                         Original Post: </br>
                                         %s </br>
                                         <blockquote>%s</blockquote> </br>
                                         All the best, </br>
                                         '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums',
    'INFRACTION_PM_SUBJECT'     =>      'You have received an infraction at '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums',
));
