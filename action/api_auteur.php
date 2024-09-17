<?php

function action_api_auteur() {

	$xml = false;

	if (
		!defined('_API_HTTPS')
		or _API_HTTPS
	) {
		if ($_SERVER['SERVER_PORT'] != 443) {
			erreur_405('Please use https');
		}
	}

	$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
	if ($id_auteur < 1) {
		erreur_405('Unknown user', 401);
	}
	$method = $_SERVER['REQUEST_METHOD'];


	if ($method == 'GET') {
		$url = $_SERVER['REQUEST_URI'];

		if (preg_match(',/api/people/(.*)?/messages\/?([0-9]+)?,', $url, $regs)) {
			$login = $regs[1];
			$debut = $regs[2];

			$ret = '';

			$query = sql_select('id_auteur', 'spip_auteurs', "login='$login' && statut!='nouveau' && statut != 'poubelle'");
			if ($row = sql_fetch($query)) {
				$id_auteur = $row['id_auteur'];


				header('Content-type: application/atom+xml; charset=utf-8');
				if ($debut < 0) {
					echo microcache($id_auteur, 'noisettes/atom_messages_auteur');
				} else {
					$contenu = recuperer_fond(
						'noisettes/atom_messages_auteur',
						[
							'id' => $id_auteur,
							'debut_messages' => $debut
						]
					);
					echo $contenu;
				}
			} else {
				erreur_405("$login not found");
			}
		} else {
			erreur_405('Wrong URL');
		}
	} else {
		erreur_405('Unknow method - use GET to retrieve data');
	}


	exit();
}
