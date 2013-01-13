<?php

function action_bouton_follow_url() {
	$id_syndic = intval(_request("id_syndic"));
	$id_follow = $GLOBALS['auteur_session']['id_auteur'];

	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé


	$query = sql_select("lang", "spip_auteurs", "id_auteur=$id_follow");
	if ($row = sql_fetch($query)) {
		lang_select($row["lang"]);
	}


	
	$follow = _request("follow");
	if ($follow == "non" OR $follow == "oui") {
		$statut_session = $GLOBALS["auteur_session"]["statut"];
	
		if ($id_follow < 1) die();
	
		
		if ($id_syndic < 1) die();
		
		
		$retour = $_SERVER["HTTP_REFERER"];	
	
		sql_query("DELETE FROM `spip_me_follow_url` WHERE `id_follow` = $id_follow AND `id_syndic` = $id_syndic");
		
		if ($follow == "oui") {
			sql_insertq("spip_me_follow_url", array(
				"id_follow" => $id_follow,
				"id_syndic" => $id_syndic,
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
	
	
	if ($id_follow < 1) die("");

	$query_lien = sql_select("id_syndic", "spip_me_follow_url", "id_follow=$id_follow AND id_syndic=$id_syndic");
	if ($row_lien = sql_fetch($query_lien)) {
		echo "<div>"._T("seenthis:suivre_url_you")."</div>";
		echo "<a href='#' class='no' onclick=\"$('#follow_url').load('index.php?action=bouton_follow_url&follow=non&id_syndic=$id_syndic'); return false;\">"._T("seenthis:suivre_url_stop")."</a>";
	} else {
		echo "<a href='#' class='yes' onclick=\"$('#follow_url').load('index.php?action=bouton_follow_url&follow=oui&id_syndic=$id_syndic'); return false;\">"._T("seenthis:suivre_url")."</a>";
	}
}

?>