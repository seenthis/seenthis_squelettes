<?php

function action_bouton_follow_mot() {
	$tag = strval(_request("tag"));
	if (!(strlen($tag))) return;

	$id_follow = $GLOBALS['auteur_session']['id_auteur'];
	if ($id_follow < 1) die("");

	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé

	$query = sql_select("lang", "spip_auteurs", "id_auteur=$id_follow");
	if ($row = sql_fetch($query)) {
		lang_select($row["lang"]);
	}

	// A quel id_mot OLD STYLE correspond ce tag
	if (substr($tag,0,1) == '#') {
		$titre = substr($tag,1);
		$type = 'Hashtags';
	} elseif (strpos(':', $tag)) {
		list($type,$titre) = explode(':',$tag);
	} else {
		$titre = $tag;
		$type = '?';
	}
	if ($f = sql_fetsel('id_mot', 'spip_mots AS m LEFT JOIN spip_groupes_mots AS g ON m.id_groupe=g.id_groupe', 'm.titre='.sql_quote($titre).' AND g.titre='.sql_quote($type)))
		$id_mot = $f['id_mot'];
	// fin OLD STYLE

	//
	// gerer une action : suivre/cesser de suivre
	//
	$follow = _request("follow");

	if ($follow == "non" OR $follow == "oui") {

		sql_query("DELETE FROM spip_me_follow_mot WHERE id_follow=$id_follow
			AND id_mot=$id_mot");
		sql_query("DELETE FROM spip_me_follow_tag WHERE id_follow=$id_follow
			AND tag=".sql_quote($tag));
		
		if ($follow == "oui") {
			if ($id_mot)
			sql_insertq("spip_me_follow_mot", array(
				"id_follow" => $id_follow,
				"id_mot" => $id_mot,
				"date" => "NOW()"
			));
			sql_insertq("spip_me_follow_tag", array(
				"id_follow" => $id_follow,
				"tag" => $tag,
				"date" => "NOW()"
			));
		}
		
		supprimer_microcache($id_follow, "noisettes/auteur_follow_people");
		supprimer_microcache($id_follow, "noisettes/auteur_follow_people_big");
		supprimer_microcache($id_follow, "noisettes/auteur_followed");
		cache_auteur($id_follow);
		
		supprimer_microcache($id_auteur, "noisettes/auteur_follow_people");
		supprimer_microcache($id_auteur, "noisettes/auteur_follow_people_big");
		supprimer_microcache($id_auteur, "noisettes/auteur_followed");
		cache_auteur($id_auteur);
	}


	//
	// renvoyer le bouton en fonction de l'état du suivi
	//
	$f = sql_fetsel('id_follow', 'spip_me_follow_tag', 'id_follow='.$id_follow
		.' AND tag='.sql_quote($tag)
	);

	if ($f) {
		echo "<div>"._T("seenthis:auteur_vous_suivez", array("people" => htmlspecialchars($tag)))."</div>";
		echo "<a href='#' class='no' onclick=\"$('#follow_mot').load('index.php?action=bouton_follow_mot&follow=non&tag=".urlencode($tag)."'); return false;\">"._T("seenthis:auteur_ne_plus_suivre", array("people"=>"<strong>$titre</strong>"))."</a>";
	} else {
		echo "<a href='#' class='yes' onclick=\"$('#follow_mot').load('index.php?action=bouton_follow_mot&follow=oui&tag=".urlencode($tag)."'); return false;\">"._T("seenthis:suivre_people", array("people"=>"<strong>".htmlspecialchars($tag)."</strong>"))."</a>";
	}
}

?>