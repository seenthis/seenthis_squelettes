<?php


function action_favori_dist() {


	$auteur_session = $GLOBALS["auteur_session"]["id_auteur"];
	$id_me = floor(_request("id_me"));


	if ($auteur_session < 1) exit;
	cache_me($id_me);

	$query = sql_select("*", "spip_me_share", "id_me=$id_me AND id_auteur=$auteur_session");
	if (sql_fetch($query)) {
		//ECHO "SUPPRIMER";
		sql_query("DELETE FROM spip_me_share WHERE id_me=$id_me AND id_auteur=$auteur_session");
	} else {
		//echo "AJOUTER";
		sql_insertq("spip_me_share",
			array(
				"id_me" => $id_me,
				"id_auteur" => $auteur_session
			)
		);
	}
	cache_me($id_me);
	
	$page = recuperer_fond("noisettes/afficher_message",array("id"=>$id_me),array('trim'=>false));

	echo $page;
	
	exit;
}



?>