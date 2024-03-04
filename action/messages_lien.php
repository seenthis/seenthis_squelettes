<?php

/**
 * Récupère les éventuels urls des messages qui pointent vers un lien
 */
function action_messages_lien() {
	$auteur_session = $GLOBALS['visiteur_session']['id_auteur'];
	if ($auteur_session < 1) {
		exit;
	}

	$url = rawurldecode(_request('url'));
	$url_messages = [];
	// virer le http/https en début d'url + le slash final
	$lien_flou = preg_replace(',/$,', '', preg_replace(',^(https?://)?,i', '', $url));
	$id_possibles = sql_allfetsel('id_me', 'spip_me_tags', 'class = ' . sql_quote('url') . ' and (tag = ' . sql_quote('http://' . $lien_flou) . ' or tag = ' . sql_quote('https://' . $lien_flou) . ')');
	$id_publies = sql_allfetsel(
		'id_me',
		'spip_me',
		["statut = 'publi'", sql_in('id_me', array_map('array_pop', $id_possibles))]
	);
	foreach ($id_publies as $k => $row) {
		$url_messages[] = url_absolue(generer_objet_url($row['id_me'], 'me', '', '', true));
	}
	header('Content-Type: text/json; charset=utf-8');
	header('Cache-Control: public, max-age=60');
	echo json_encode(['urlMessages' => $url_messages]);
}
