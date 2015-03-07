<?php

/**
 * Récupère les éventuels urls des messages qui pointent vers un lien
 */
function action_messages_lien() {
	$auteur_session = $GLOBALS["visiteur_session"]["id_auteur"];
	if ($auteur_session < 1) exit;

	$url = rawurldecode(_request("url"));
	spip_log($url);
	$url_messages = array();
	$query = sql_allfetsel(
		'spip_me.id_me as id_me',
		"spip_me where statut = 'publi' and id_me in (select spip_me_syndic.id_me from spip_me_syndic where spip_me_syndic.id_syndic in (select spip_syndic.id_syndic from spip_syndic where spip_syndic.url_site = ".sql_quote($url)."))", '');
	foreach ($query as $k => $row) {
		$url_messages[] = "http://"._HOST."/messages/".$row['id_me'];
	}
	header('Content-Type: text/json; charset=utf-8');
	header("Cache-Control: public, max-age=60");
	echo json_encode(array('urlMessages' => $url_messages));
}

?>