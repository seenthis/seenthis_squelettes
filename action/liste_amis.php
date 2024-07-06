<?php

/**
 * Récupère la liste des login des gens qu'on suit et qui nous suivent sous forma json
 */
function action_liste_amis() {
	$auteur_session = $GLOBALS['visiteur_session']['id_auteur'];
	if ($auteur_session < 1) {
		exit;
	}

	$logins = [];
	$query = sql_allfetsel('spip_auteurs.login as login', "spip_auteurs where spip_auteurs.id_auteur in (select spip_me_follow.id_auteur from spip_me_follow where id_follow = $auteur_session union select id_follow from spip_me_follow where id_auteur = $auteur_session)", '');
	foreach ($query as $k => $row) {
		$logins[] = $row['login'];
	}
	header('Content-Type: text/json; charset=utf-8');
	header('Cache-Control: public, max-age=3600');
	echo json_encode(['logins' => $logins]);
}
