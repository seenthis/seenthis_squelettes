<?php

function _menage($id_auteur, $id_follow) {
		job_queue_add('calculer_troll', 'Troll auteur '.$id_auteur, array($id_auteur, true));		
		
		supprimer_microcache($id_follow, "noisettes/auteur_follow_people");
		supprimer_microcache($id_follow, "noisettes/auteur_follow_people_big");
		supprimer_microcache($id_follow, "noisettes/auteur_followed");
		cache_auteur($id_follow);
		
		supprimer_microcache($id_auteur, "noisettes/auteur_follow_people");
		supprimer_microcache($id_auteur, "noisettes/auteur_follow_people_big");
		supprimer_microcache($id_auteur, "noisettes/auteur_followed");
		cache_auteur($id_auteur);
}

function action_bouton_follow_people() {
	$id_auteur = _request("id_auteur");
	$id_follow = $GLOBALS['auteur_session']['id_auteur'];
	$id_block = $id_follow;
	$id_discard = $id_follow;

	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé

	$query = sql_select("lang", "spip_auteurs", "id_auteur=$id_follow");
	if ($row = sql_fetch($query)) {
		lang_select($row["lang"]);
	}

	$follow = _request("follow");
	$block = _request("block");
	$discard = _request("discard");
	
	if($discard == "oui" OR $discard == "non") {
		$statut_session = $GLOBALS["auteur_session"]["statut"];
		if ($id_discard < 1) die();
		$id_auteur = floor(_request("id_auteur"));
		if ($id_auteur < 1) die();

		if($discard = "oui") {
			$session_discard = session_get('discard');
			if(!isset($session_discard)) $session_discard = array();
			if(array_push($session_discard, $id_auteur)) {
				session_set('discard', $session_discard);
			}
		}

		_menage($id_auteur, $id_follow);

	}

	if ($block == "oui" OR $block == "non") {
		$statut_session = $GLOBALS["auteur_session"]["statut"];
		if ($id_block < 1) die();
		$id_auteur = floor(_request("id_auteur"));
		if ($id_auteur < 1) die();
		
		$retour = $_SERVER["HTTP_REFERER"];	
		
		sql_query("DELETE FROM `spip_me_follow` WHERE `id_follow` = $id_follow AND `id_auteur` = $id_auteur");
		sql_query("DELETE FROM `spip_me_block` WHERE `id_block` = $id_block AND `id_auteur` = $id_auteur");
		
		if ($block == "oui") {
			sql_insertq("spip_me_block", array(
				"id_block" => $id_block,
				"id_auteur" => $id_auteur,
				"date" => "NOW()"
			));
			
			//die("Bloqué $id_block $id_auteur");
			//job_queue_add('notifier_suivre_moi', "notifier_suivre_moi $id_auteur - $id_follow", array($id_auteur, $id_follow));
		}

		_menage($id_auteur, $id_follow);

	}
	
	
	if ($follow == "non" OR $follow == "oui") {
		$statut_session = $GLOBALS["auteur_session"]["statut"];
		
		if ($id_follow < 1) die();
	
		$id_auteur = floor(_request("id_auteur"));
		
		if ($id_auteur < 1) die();
		
		
		$retour = $_SERVER["HTTP_REFERER"];	
	
		sql_query("DELETE FROM `spip_me_follow` WHERE `id_follow` = $id_follow AND `id_auteur` = $id_auteur");
		sql_query("DELETE FROM `spip_me_block` WHERE `id_block` = $id_block AND `id_auteur` = $id_auteur");
		
		if ($follow == "oui") {
			sql_insertq("spip_me_follow", array(
				"id_follow" => $id_follow,
				"id_auteur" => $id_auteur,
				"date" => "NOW()"
			));
			job_queue_add('notifier_suivre_moi', "notifier_suivre_moi $id_auteur - $id_follow", array($id_auteur, $id_follow));
			//notifier_suivre_moi($id_auteur, $id_follow);
			
		}

		_menage($id_auteur, $id_follow);

	}	
	
	
	if ($id_follow < 1) die("");

	$query = sql_select("nom", "spip_auteurs", "id_auteur=$id_auteur");
	if ($row = sql_fetch($query)) {
		include_spip("inc/texte");
		$nom = typo($row["nom"]);

		$query_lien = sql_select("id_auteur", "spip_me_follow", "id_follow=$id_follow AND id_auteur=$id_auteur");
		if ($row_lien = sql_fetch($query_lien)) {
			echo "<div>"._T("seenthis:auteur_vous_suivez", array("people" => $nom))."</div>";
			echo "<a href='#' class='no' onclick=\"$('#follow').load('index.php?action=bouton_follow_people&follow=non&id_auteur=$id_auteur'); return false;\">"._T("seenthis:auteur_ne_plus_suivre", array("people"=>"<strong>$nom</strong>"))."</a>";
		} else {
			echo "<a href='#' class='yes' onclick=\"$('#follow').load('index.php?action=bouton_follow_people&follow=oui&id_auteur=$id_auteur'); return false;\">"._T("seenthis:suivre_people", array("people"=>"<strong>$nom</strong>"))."</a>";
		}

		echo "<br style='clear:both;'><br>";

		$query_block = sql_select("id_auteur", "spip_me_block", "id_block=$id_block AND id_auteur=$id_auteur");
		if ($row_block = sql_fetch($query_block)) {
			echo "<div class='no'>"._T("seenthis:auteur_vous_block", array("people" => $nom))."</div>";
			echo "<a href='#' class='yes' onclick=\"$('#follow').load('index.php?action=bouton_follow_people&block=non&id_auteur=$id_auteur'); return false;\">"._T("seenthis:auteur_ne_plus_block", array("people"=>"<strong>$nom</strong>"))."</a>";
		} else {
			echo "<a href='#' class='no' onclick=\"$('#follow').load('index.php?action=bouton_follow_people&block=oui&id_auteur=$id_auteur'); return false;\">"._T("seenthis:auteur_block", array("people"=>"<strong>$nom</strong>"))."</a>";
		}
	}
}

?>