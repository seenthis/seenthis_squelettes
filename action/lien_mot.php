<?php


function action_lien_mot() {


	$auteur_session = $GLOBALS["auteur_session"]["id_auteur"];
	$relation = _request("relation");
	$statut = _request("statut");
	
	if ($statut == "desactiver") $statut = "oui";
	else if ($statut == "activer") $statut = "non";

	if ($auteur_session < 1) exit;
	$autoriser = false;

	
	if (preg_match(",mot([0-9]+)\-me([0-9]+)(\-syndic([0-9]+))?,", $relation, $regs)) {
	
		$id_mot = $regs[1];
		$id_me = $regs[2];
		$id_syndic = $regs[4];

		if ($id_me > 0) {
			echo "ME";
			$query = sql_select("id_auteur", "spip_me", "id_me=$id_me AND id_auteur=$auteur_session");
			$autoriser = true;
			if ($row = sql_fetch($query)) {
				sql_updateq(
					"spip_me_mot", 
					array("off"=>"$statut"), 
					"id_me=$id_me AND id_mot=$id_mot"
				);
			}
		}
		
		if ($id_syndic > 0 && $autoriser) {
			echo "SYNDIC";
				sql_updateq(
					"spip_syndic_oc", 
					array("off"=>"$statut"), 
					"id_syndic=$id_syndic AND id_mot=$id_mot"
				);
		}
		
		if ($id_me > 0 && $autoriser) cache_me($id_me);
	}




	
	exit;
}



?>