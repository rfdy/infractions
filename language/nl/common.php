<?php
/**
*
* Infractions extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBBservice.nl <http://www.phpbbservice.nl>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
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

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//
$lang = array_merge($lang, array(
	'ACP_DEMO_GOODBYE'					=> 'Zeg maar dag?',
	'ACP_INF_MANAGER'					=> 'Overtredingen',
	'ACP_INF_RULES'						=> 'Overtredingregels',
	'ACP_INF_STATS'						=> 'Overtredingstatistieken',
	'ACP_INFRACTION_SAVED'				=> 'Overtreding is succesvol opgeslagen!',
	'ACP_MANAGE_INFRACTIONS'			=> 'Beheer overtredingen',
	'ACP_MANAGE_RULES'					=> 'Beheer overtredingregels',
	'ACP_RULE_MANAGER'					=> 'Overtredingregels',
	'ACP_RULE_SAVED'					=> 'Regel is succesvol opgeslagen!',
	'ACP_SAVE_DUPLICATE'				=> 'Dubbel: Regel bestaat al',
	'ADD_NEW_INFRACTION'				=> 'Nieuwe overtreding toevoegen',
	'UPDATE_INFRACTIONS_RULES_URL'		=> 'Regels-URL bijgewerkt',
	'ADD_NEW_RULE'						=> 'Nieuwe regel toevoegen',
	'ACL_A_INFRACTIONS'					=> 'Kan overtredingen en regels beheren',
	'ACL_CAT_INFRACTIONS'				=> 'Overtredingen',
	'ACL_M_INFRACTIONS_ADD'				=> 'Kan boetes uitvaardigen',
	'ACL_M_INFRACTIONS_REMOVE'			=> 'Kan een bestaande boete verwijderen',
	'ACL_NO_GROUPS'						=> 'Geen eigen groepen beschikbaar',
	'MULTIPLE_INFRACTIONS'				=> 'Waarschuwing: Gebruiker heeft al een boete gekregen voor het volgende bericht:',
	'MISSING_FIELDS'					=> 'Fout:Missende of foutieve data in velden.',
	'CANNOT_ISSUE_SELF_INFRACTION'		=> 'Cannot issue infraction to self',
	'EXPIRE_INFRACTIONS'				=> 'Afgelopen boetes verwijderd.',
	'RECALCULATE_RULE_PENALTIES'		=> 'Herbereken regelboetes',
	'USER_INFRACTION_ADDED'				=> 'Boete is succesvol uitgevaardigd.',
	'GO_TO_INFRACTION_LIST'				=> '%s Bekijk lijst van alle overtredingen %s',
	'LOG_ISSUED_INFRACTION'				=> '<strong>Boete uitgevaardigd aan gebruiker</strong><br />» %s',
	'LOG_CANCELLED_INFRACTION'			=> '<strong>Boete gebruiker opgeheven</strong>',
	'INFRACTION_PM_BODY_USER'			=> 'Beste %s, <br />
		Je hebt een boete gekregen op  '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums. <br /><br />
		<strong>Overtreding:</strong> %s <br />
		<strong>Reden:</strong> %s <br /><br />
		Deze overtreding is %s punt(en) waard en kan resulteren in beperkte toegang totdat deze overtreding verloopt. Serieuze overtredingen zullen nooit verlopen.<br />
		<strong>Je overtredingengeschiedenis is alleen zichtbaar voor jezelf en het moderatie-team.</strong><br />
		Lees nogmaals de regels zodat je toekomstige overtredingen kan voorkomen:<br />
		'.$RULES_URL_TO_REPLACE_INFRACTIONS.' <br />
		Alleen een beheerder kan een overtreding terugdraaien en het zal alleen gedaan worden als een overtreding foutief is gegeven. Stuur een privébericht naar een <strong>beheerder</strong> als je denkt dat er een fout is opgetreden.<br />
		Met vriendelijke groeten,<br />
		'.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums',
	'INFRACTION_PM_BODY_POST'			=> 'Beste %s, <br />
		Je hebt een boete gekregen op '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums.<br /><br />
		<strong>Overtreding:</strong> %s<br />
		<strong>Reden:</strong> %s</br><br />
		Deze overtreding is %s punt(en) waard en kan resulteren in beperkte toegang totdat deze overtreding verloopt. Serieuze overtredingen zullen nooit verlopen.<br />
		<strong>Je overtredingengeschiedenis is alleen zichtbaar voor jezelf en het moderatie-team.</strong><br />
		Lees nogmaals de regels zodat je toekomstige overtredingen kan voorkomen:<br />
		'.$RULES_URL_TO_REPLACE_INFRACTIONS.'<br />
		Alleen een beheerder kan een overtreding terugdraaien en het zal alleen gedaan worden als een overtreding foutief is gegeven. Stuur een privébericht naar een <strong>beheerder</strong> als je denkt dat er een fout is opgetreden.<br />
		Origineel bericht:<br />
		%s<br />
		<blockquote>%s</blockquote><br />
		Met vriendelijke groeten,<br />
		'.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums',
	'INFRACTION_PM_SUBJECT'			=> 'Je hebt een boete ontvangen op '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums',
));
