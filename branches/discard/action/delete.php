<?php


function action_delete_dist() {

	$auteur_session = $GLOBALS["auteur_session"]["id_auteur"];
	$statut_session = $GLOBALS["auteur_session"]["statut"];
	$id_me = floor(_request("id_me"));
	
	$retour = $_SERVER["HTTP_REFERER"];	

		
	

	$autoriser_supprimer = false;
	
	// Je suis l'admin, quand meme !
	// if ($statut_session == "0minirezo") $autoriser_supprimer = true;

	$query_auteur = sql_query("SELECT id_auteur, id_parent FROM spip_me WHERE id_me = $id_me");
	if ($row_auteur = sql_fetch($query_auteur)) {
		$id_auteur = $row_auteur["id_auteur"];
		
		$id_parent = $row_auteur["id_parent"];
		
		if ($id_auteur == $auteur_session) $autoriser_supprimer = true;
	}

	if (!$autoriser_supprimer && $id_parent > 0) {	
		$query_auteur = sql_query("SELECT id_auteur FROM spip_me WHERE id_me = $id_parent");
		if ($row_auteur = sql_fetch($query_auteur)) {
			$id_auteur = $row_auteur["id_auteur"];
			
			$id_parent = $row_auteur["id_parent"];
			
			if ($id_auteur == $auteur_session) $autoriser_supprimer = true;
		}
	}


	if ($autoriser_supprimer) supprimer_me($id_me);
	
	
	//header("Location:".$retour);
	exit;
}



?>