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
	'ACP_DEMO_GOODBYE'			     => '¿Debe decir adiós?',
    'ACP_INF_MANAGER'			     => 'Infracciones',
    'ACP_INF_RULES'			         => 'Normas de infracción',
    'ACP_INF_STATS'                  => 'Estadísticas de infracción',
	'ACP_INFRACTION_SAVED'	         => '¡La infracción ha sido guardada correctamente!',
    'ACP_MANAGE_INFRACTIONS'         => 'Gestionar infracciones',
    'ACP_MANAGE_RULES'               => 'Gestionar normas de infracciones',
    'ACP_RULE_MANAGER'		      	 => 'Normas de infracciones',
    'ACP_RULE_SAVED'                 => '¡La norma ha sido guardada correctamente!',
    'ACP_SAVE_DUPLICATE'             => 'Duplicada: Ya existe esa norma',
    'ADD_NEW_INFRACTION'             => 'Añadir nueva infracción',
    'UPDATE_INFRACTIONS_RULES_URL'   => 'Actualizar URL de normas',
    'ADD_NEW_RULE'                   => 'Añadir nueva norma',
    'ACL_A_INFRACTIONS'              => 'Puede gestionar infracciones y normas',
    'ACL_CAT_INFRACTIONS'            => 'Infracciones',
    'ACL_M_INFRACTIONS_ADD'          => 'Puede emitir una infracción',
    'ACL_M_INFRACTIONS_REMOVE'       => 'Puede eliminar una infracción existente',
    'ACL_NO_GROUPS'                  => 'No hay grupos personalizados disponibles',
    'MULTIPLE_INFRACTIONS'           => 'Advertencia: Ya se ha emitido una infracción al usuario por el siguiente mensaje:',
    'MISSING_FIELDS'                 => 'Error: Faltan, o datos incorrectos en los campos.',
    'CANNOT_ISSUE_SELF_INFRACTION'	 => 'No puede emitir una infracción a usted mismo',
    'EXPIRE_INFRACTIONS'             => 'Eliminar infracciones caducadas.',
    'RECALCULATE_RULE_PENALTIES'     => 'Recalcular sanciones de las normas',
    'USER_INFRACTION_ADDED'          => 'La infracción se ha emitido correctamente.',
    'GO_TO_INFRACTION_LIST'          => '%s Ver lista de todas las infracciones %s',
    'LOG_ISSUED_INFRACTION'	         => '<strong>Infracción del usuario emitida</strong><br />» %s',
    'LOG_CANCELLED_INFRACTION'	     => '<strong>Infracción del usuario cancelada</strong>',
    'INFRACTION_PM_BODY_USER'        => 'Querido/a %s, </br>
                                         Usted ha recibido una infracción en los foros de '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.'. </br></br>
                                         <strong>Infracción:</strong> %s </br>
                                         <strong>Razón:</strong> %s </br></br>
                                         Esta infracción tiene un valor de %s punto(s) y puede resultar en una restricción de acceso hasta que expire. Las infracciones graves nunca expirarán. </br>
                                         <strong>Su historial de infracción solo es visible para usted y el equipo de Moderadores.</strong></br>
                                         Por favor, revise las normas para evitar otras infracciones en el futuro: </br>
                                         '.$RULES_URL_TO_REPLACE_INFRACTIONS.' </br>
                                         Sólo un Administrador puede retirar una infracción, y sólo se hace si una infracción se le dio por error. Mensaje privado para un <strong>Administrador</strong> si cree que ha ocurrido un error. </br>
                                         Saludos cordiales, </br>
                                         '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums',
    'INFRACTION_PM_BODY_POST'        => 'Querido/a %s, </br>
                                         Usted ha recibido una infracción en los foros de '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.'. </br></br>
                                         <strong>Infracción:</strong> %s </br>
                                         <strong>Razón:</strong> %s </br>
                                         Esta infracción tiene un valor de %s punto(s) y puede resultar en una restricción de acceso hasta que expire. Las infracciones graves nunca expirarán. </br>
                                         <strong>Su historial de infracción solo es visible para usted y el equipo de Moderadores.</strong></br>
                                         Por favor, revise las normas para evitar otras infracciones en el futuro: </br>
                                         '.$RULES_URL_TO_REPLACE_INFRACTIONS.' </br>
                                         Sólo un Administrador puede retirar una infracción, y sólo se hace si una infracción se le dio por error. Mensaje privado para un <strong>Administrador</strong> si cree que ha ocurrido un error. </br>
                                         Mensaje Original: </br>
                                         %s </br>
                                         <blockquote>%s</blockquote> </br>
                                         Saludos cordiales, </br>
                                         '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' Forums',
    'INFRACTION_PM_SUBJECT'     =>      'Usted ha recibido una infracción en los foros de '.$FORUM_NAME_TO_REPLACE_INFRACTIONS.' ',
));
