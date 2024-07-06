<?php

function action_bouton_follow_mot() {
	$tag = strval(_request('tag'));
	if (!(strlen($tag))) {
		return;
	}

	$id_follow = $GLOBALS['auteur_session']['id_auteur'];
	if ($id_follow < 1) {
		die('');
	}

	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date dans le passé

	$query = sql_select('lang', 'spip_auteurs', "id_auteur=$id_follow");
	if ($row = sql_fetch($query)) {
		lang_select($row['lang']);
	}

	//
	// gerer une action : suivre/cesser de suivre
	//
	$follow = _request('follow');

	if ($follow == 'non' or $follow == 'oui') {
		sql_query("DELETE FROM spip_me_follow_tag WHERE id_follow=$id_follow
			AND tag=" . sql_quote($tag));

		if ($follow == 'oui') {
			sql_insertq('spip_me_follow_tag', [
				'id_follow' => $id_follow,
				'tag' => $tag,
				'date' => 'NOW()'
			]);
		}

		supprimer_microcache($id_follow, 'noisettes/auteur_follow_people');
		supprimer_microcache($id_follow, 'noisettes/auteur_follow_people_big');
		supprimer_microcache($id_follow, 'noisettes/auteur_followed');
		cache_auteur($id_follow);

	}


	//
	// renvoyer le bouton en fonction de l'état du suivi
	//
	$f = sql_fetsel('id_follow', 'spip_me_follow_tag', 'id_follow=' . $id_follow
		. ' AND tag=' . sql_quote($tag));

	if ($f) {
		echo '<div>' . _T('seenthis:auteur_vous_suivez', ['people' => htmlspecialchars($tag)]) . '</div>';
		echo "<a href='#' class='no' onclick=\"$('#follow_mot').load('index.php?action=bouton_follow_mot&follow=non&tag=" . urlencode($tag) . "'); return false;\">" . _T('seenthis:auteur_ne_plus_suivre', ['people' => '<strong>' . htmlspecialchars($tag) . '</strong>']) . '</a>';
	} else {
		echo "<a href='#' class='yes' onclick=\"$('#follow_mot').load('index.php?action=bouton_follow_mot&follow=oui&tag=" . urlencode($tag) . "'); return false;\">" . _T('seenthis:suivre_people', ['people' => '<strong>' . htmlspecialchars($tag) . '</strong>']) . '</a>';
	}
}
