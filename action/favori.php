<?php

function action_favori_dist() {

	$auteur_session = $GLOBALS['visiteur_session']['id_auteur'];

	if ($auteur_session < 1) {
		exit;
	}

	// on verifie si le msg existe ; le cas echeant, on partagera son id_parent
	$me = sql_allfetsel('id_me,id_parent', 'spip_me', 'id_me=' . sql_quote(_request('id_me')));
	if (!$me) {
		return;
	}
	if ($me[0]['id_parent']) {
		$id_me = $me[0]['id_parent'];
	} else {
		$id_me = $me[0]['id_me'];
	}

	// il est deja partage ?
	$deja = count($a = sql_allfetsel('*', 'spip_me_share', "id_me=$id_me AND id_auteur=$auteur_session"));

	// veut-on "partager" ou "arreter de" ?
	// si on ne precise pas, ca bascule
	if (!$share = intval(_request('share'))) {
		$share = $deja ? -1 : +1;
	}

	// partager : ajouter un lien si pas deja
	if ($share == 1 and !$deja) {
		//echo "AJOUTER";
		sql_insertq(
			'spip_me_share',
			[
				'id_me' => $id_me,
				'id_auteur' => $auteur_session
			]
		);
		job_queue_add(
			'notifier_partage',
			"notifier_partage $auteur_session - $id_me",
			[$auteur_session, $id_me],
			'inc/seenthis_notifier',
			/* no_duplicate */ true,
			/* time */ time() + 300   // accorder 5 minutes de delai
		);
	}

	// partager : supprimer tous les liens
	if ($share == -1 and $deja) {
		//echo "SUPPRIMER";
		sql_query("DELETE FROM spip_me_share WHERE id_me=$id_me AND id_auteur=$auteur_session");
	}

	cache_message($id_me);

	$page = recuperer_fond('noisettes/message/afficher_message', ['id' => $id_me], ['trim' => false]);

	echo $page;

	exit;
}
