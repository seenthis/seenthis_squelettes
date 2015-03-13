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
	$id_possibles = sql_allfetsel('id_me', 'spip_me_tags', 'class = '.sql_quote('url').' and tag = '.sql_quote(preg_replace(',/$,', '', $url)));
	$id_publies = sql_allfetsel(
		'id_me',
		'spip_me', array("statut = 'publi'", sql_in('id_me', array_map('array_pop', $id_possibles))));
	foreach ($id_publies as $k => $row) {
		$url_messages[] = "http://"._HOST."/messages/".$row['id_me'];
	}
	header('Content-Type: text/json; charset=utf-8');
	header("Cache-Control: public, max-age=60");
	echo json_encode(array('urlMessages' => $url_messages));
}

?>