<?php

function action_bouton_follow_mot() {
	$id_mot = _request("id_mot");
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
	
		
		if ($id_mot < 1) die();
		
		
		$retour = $_SERVER["HTTP_REFERER"];	
	
		sql_query("DELETE FROM `spip_me_follow_mot` WHERE `id_follow` = $id_follow AND `id_mot` = $id_mot");
		
		if ($follow == "oui") {
			sql_insertq("spip_me_follow_mot", array(
				"id_follow" => $id_follow,
				"id_mot" => $id_mot,
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

	$query = sql_select("titre", "spip_mots", "id_mot=$id_mot");
	if ($row = sql_fetch($query)) {
		include_spip("inc/texte");
		$titre = typo($row["titre"]);

		$query_lien = sql_select("id_mot", "spip_me_follow_mot", "id_follow=$id_follow AND id_mot=$id_mot");
		if ($row_lien = sql_fetch($query_lien)) {
			echo "<div>"._T("seenthis:auteur_vous_suivez", array("people" => $titre))."</div>";
			echo "<a href='#' class='no' onclick=\"$('#follow_mot').load('index.php?action=bouton_follow_mot&follow=non&id_mot=$id_mot'); return false;\">"._T("seenthis:auteur_ne_plus_suivre", array("people"=>"<strong>$titre</strong>"))."</a>";
		} else {
			echo "<a href='#' class='yes' onclick=\"$('#follow_mot').load('index.php?action=bouton_follow_mot&follow=oui&id_mot=$id_mot'); return false;\">"._T("seenthis:suivre_people", array("people"=>"<strong>$titre</strong>"))."</a>";
		}

	}
}

?>