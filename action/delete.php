<?php

function action_delete_dist() {

	$id_me = floor(_request('id_me'));
	$retour = $_SERVER['HTTP_REFERER'];

	include_spip('inc/autoriser');
	if (autoriser('supprimer', 'me', $id_me)) {
		supprimer_me($id_me);
	}

	//header("Location:".$retour);
	exit;
}
