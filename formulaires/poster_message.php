<?php

############## CHARGER

function formulaires_poster_message_charger ($id_me=0, $id_parent=0, $id_dest=0, $ze_mot=0) {
	if (!$GLOBALS["visiteur_session"]["id_auteur"])
		return;

	$texte_message = "";

	if ($id_me > 0) {
		$query = sql_select("*", "spip_me_texte", "id_me=".intval($id_me));
		if ($row = sql_fetch($query)) {
			$texte_message = $row["texte"];
		}
	}

	$valeurs = Array(
		"texte_message"=> "$texte_message",
		"id_dest" => intval($id_dest),
		"ze_mot" => intval($ze_mot),
		"id_auteur" => $GLOBALS["visiteur_session"]["id_auteur"],
		'action' => preg_replace(',^[^/]+://[^/]+/,', '/', url_de_base()),
	);

	return $valeurs;

}


################# VERIFIER

function formulaires_poster_message_verifier ($id_me = 0, $id_parent=0, $id_dest=0, $ze_mot=0){
	if (!$GLOBALS["visiteur_session"]["id_auteur"])
		return;

	$errors = Array();
	
	
	$texte_message = _request("texte_message");
	
	if  ( strlen(trim($texte_message)) == 0 ) $errors["texte_message"] = "You have to write something before sending.";
	
	
	if ($id_parent > 0) {
		$query_auteur = sql_select("*", "spip_me", "id_me=".intval($id_parent));
		if ($row_auteur = sql_fetch($query_auteur)) {
			$id_block = $row_auteur["id_auteur"];
			$id_auteur = floor($GLOBALS["visiteur_session"]["id_auteur"]);
			
			//die("$id_block - $id_auteur");
			
			$query = sql_select("*", "spip_me_block", "id_block=$id_block AND id_auteur=$id_auteur");
			if ($row = sql_fetch($query)) {
				$errors["texte_message"] = _T("seenthis:auteur_block_you");
			}
		}
	}
	
	
	
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
	
	$id_auteur = floor($GLOBALS["visiteur_session"]["id_auteur"]);

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