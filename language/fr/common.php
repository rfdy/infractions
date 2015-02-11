<?php
/**
 *
 * @package phpBB Extension - Infractions
 * @copyright (c) 2015 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 * French translation by Galixte (http://www.galixte.com)
 *
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

$lang = array_merge($lang, array(
	'ACP_DEMO_GOODBYE'			     => 'Vouliez-vous dire au revoir ?',
    'ACP_INF_MANAGER'			     => 'Infractions',
    'ACP_INF_RULES'			         => 'Règles des infractions',
    'ACP_INF_STATS'                  => 'Statistiques des infractions',
	'ACP_INFRACTION_SAVED'	         => 'L’infraction a été sauvegardée avec succès !',
    'ACP_MANAGE_INFRACTIONS'         => 'Gérer les infractions.',
    'ACP_MANAGE_RULES'               => 'Gérer les règles des infractions',
    'ACP_RULE_MANAGER'		      	 => 'Règles des infractions',
    'ACP_RULE_SAVED'                 => 'La règle a été sauvegardée avec succès !',
    'ACP_SAVE_DUPLICATE'             => 'Dupliquer : La règle existe déjà',
    'ADD_NEW_INFRACTION'             => 'Ajouter une nouvelle infraction',
    'UPDATE_INFRACTIONS_RULES_URL'   => 'Mettre à jour les règles des adresses URL',
    'ADD_NEW_RULE'                   => 'Ajouter une nouvelle règle',
    'ACL_A_INFRACTIONS'              => 'Peut gérer les règles et les infractions.',
    'ACL_CAT_INFRACTIONS'            => 'Infractions',
    'ACL_M_INFRACTIONS_ADD'          => 'Peut émettre une infraction.',
    'ACL_M_INFRACTIONS_REMOVE'       => 'Peut retirer une infraction existante.',
    'ACL_NO_GROUPS'                  => 'Aucun groupe personnalisé disponible',
    'MULTIPLE_INFRACTIONS'           => 'Attention : l’utilisateur a déjà commis une infraction pour le message suivant :',
    'MISSING_FIELDS'                 => 'Erreur : Données manquantes ou erronées dans les champs.',
    'CANNOT_ISSUE_SELF_INFRACTION'            => 'Vous ne pouvez pas émettre infraction vous même',
    'EXPIRE_INFRACTIONS'             => 'Infractions expirées retirées.',
    'RECALCULATE_RULE_PENALTIES'     => "Recalculer la règle des pénalités",
    'USER_INFRACTION_ADDED'          => 'L’infraction a été émie avec succès.',
    'GO_TO_INFRACTION_LIST'          => '%s Voir la liste de toutes les infractions %s',
    'LOG_ISSUED_INFRACTION'	         => '<strong>Infraction de l’utilisateur émise</strong><br />» %s',
    'LOG_CANCELLED_INFRACTION'	     => '<strong>Infraction de l’utilisateur annulée</strong>',
    'INFRACTION_PM_BODY_USER'        => 'Chèr(e) %s, </br>
                                         Vous avez reçu une infraction dans les forums '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.'. </br></br>
                                         <strong>Infraction :</strong> %s </br>
                                         <strong>Raison :</strong> %s </br></br>
                                         Cette infraction vaut %s point(s) et peut entraîner des accès restreints jusqu’à ce qu’elle expire. Les infractions graves n’expirent jamais. </br>
                                         <strong>Votre histoire d’infractions n’est visible que par vous et l’équipe de modération.</strong></br>
                                         Veuillez examiner les règles afin d’éviter de nouvelles infractions : </br>
                                         '.$RULES_URL_TO_REPLACE_INFRACTIONS.' </br>
                                         Seul un administrateur peut annuler une infraction et cela se fera uniquement si une infraction a été émise par erreur. Envoyez un message privé à <strong>l’administrateur</strong> si vous croyez à une erreur. </br>
                                         Cordialement, </br>
                                         Le forum '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.'',
    'INFRACTION_PM_BODY_POST'        => 'Chèr(e) %s, </br>
                                         Vous avez reçu une infraction dans les forums '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.'. </br></br>
                                         <strong>Infraction :</strong> %s </br>
                                         <strong>Raison :</strong> %s </br>
                                         Cette infraction vaut %s point(s) et peut entraîner des accès restreints jusqu’à ce qu’elle expire. Les infractions graves n’expirent jamais. </br>
                                         <strong>Votre histoire d’infractions n’est visible que par vous et l’équipe de modération.</strong></br>
                                         Veuillez examiner les règles afin d’éviter de nouvelles infractions : </br>
                                         '.$RULES_URL_TO_REPLACE_INFRACTIONS.' </br>
                                         Seul un administrateur peut annuler une infraction et cela se fera uniquement si une infraction a été émise par erreur. Envoyez un message privé à <strong>l’administrateur</strong> si vous croyez à une erreur. </br>
                                         Message original : </br>
                                         %s </br>
                                         <blockquote>%s</blockquote> </br>
                                         Cordialement, </br>
                                         Le forum '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.'',
    'INFRACTION_PM_SUBJECT'     =>      'Vous avez reçu une infraction dans les forums '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.'',
));
