<?php

############## CHARGER

function formulaires_poster_message_charger ($id_me=0, $id_parent=0, $id_dest=0, $ze_mot=0) {
	$texte_message = "";

	if ($id_me > 0) {
		$query = sql_select("*", "spip_me_texte", "id_me=$id_me");
		if ($row = sql_fetch($query)) {
			$texte_message = $row["texte"];
		}
	}

	
	$valeurs = Array(
		"texte_message"=> "$texte_message",
		"id_dest" => $id_dest,
		"ze_mot" => $ze_mot,
		"id_auteur" => $GLOBALS["auteur_session"]["id_auteur"]
	);

	return $valeurs;

}


################# VERIFIER

function formulaires_poster_message_verifier ($id_me = 0, $id_parent=0, $id_dest=0, $ze_mot=0){
	$errors = Array();
	
	
	$texte_message = _request("texte_message");
	
	if  ( strlen(trim($texte_message)) == 0 ) $errors["texte_message"] = "You have to write something before sending.";
	
	/*	
	preg_match_all("/"._REG_PEOPLE."/", $texte_message, $regs);
	if ($regs) {	
		include_spip("base/abstract_sql");
	
		foreach ($regs[0] as $k=>$people) {
			$nom = substr($people, 1, 1000);
			
			$query = sql_query("SELECT id_auteur FROM spip_auteurs WHERE login = '$nom'");
			if (!sql_fetch($query)) {
				$errors["people"] .= "<div>@<b>$nom</b> is not using ".lire_meta("nom_site")."</div>";
			}
			
		
		}
	}
	*/
	
	return $errors;
}


################### TRAITER

function formulaires_poster_message_traiter ($id_me=0, $id_parent=0, $id_dest=0, $ze_mot=0){
	include_spip("base/abstract_sql");
	$maj = 0;
	$id_me_nouv = 0;
	$texte_message = _request("texte_message");
	
	$id_auteur = floor($GLOBALS["auteur_session"]["id_auteur"]);

	$ret = instance_me ($id_auteur, $texte_message,  $id_me, $id_parent, $id_dest, $ze_mot);
	$id_me_nouv = $ret["id_me"];
	$id_parent = $ret["id_parent"];
	$maj = $ret["maj"];

	if ($id_parent > 0) $pave = $id_parent;
	else $pave = $id_me_nouv;

	return array(
		'message_ok'=> array(
			'texte' => 'Message sent',
			'ok_me' => $id_me_nouv,
			'ok_parent' => $id_parent,
			'maj' => $maj,
			'pave' => $pave
		)
	);	
}


?>