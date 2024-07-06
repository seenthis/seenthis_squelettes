<?php

/**
 * Vérifie si un login existe
 */
function action_auteur_existe() {
	$auteur_session = $GLOBALS['visiteur_session']['id_auteur'];
	if ($auteur_session < 1) {
		exit;
	}

	$login_auteur = _request('login_auteur');
	$nombre = sql_getfetsel('count(*)', 'spip_auteurs', [ 'login = ' . sql_quote($login_auteur), "statut!='nouveau'", "statut != '5poubelle'" ]);
	header('Content-Type: text/json; charset=utf-8');
	header('Cache-Control: public, max-age=3600');
	echo json_encode(['result' => $nombre == 1]);
}
