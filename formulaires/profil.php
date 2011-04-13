<?php

############## CHARGER

function formulaires_profil_charger () {
	$id_auteur = $GLOBALS["auteur_session"]["id_auteur"];
	
	if ($id_auteur > 0) {
		$query = sql_select("*", "spip_auteurs", "id_auteur=$id_auteur");
		if ($row = sql_fetch($query)) {
			$nom = $row["nom"];
			$lang = $row["lang"];
			$bio = $row["bio"];
			$couleur = $row["couleur"];
			$url_site = $row["url_site"];
			$copyright = $row["copyright"];
			$mail_nouv_billet = $row["mail_nouv_billet"];
			$mail_rep_moi = $row["mail_rep_moi"];
			$mail_rep_billet = $row["mail_rep_billet"];
			$mail_rep_conv = $row["mail_rep_conv"];
		}
	}

	$valeurs = Array(
		"nom"=> "$nom",
		"lang"=> "$lang",
		"bio" => "$bio",
		"couleur" => "$couleur",
		"url_site" => "$url_site",
		"copyright" => "$copyright",
		"mail_nouv_billet" => "$mail_nouv_billet",
		"mail_rep_moi" => "$mail_rep_moi",
		"mail_rep_billet" => "$mail_rep_billet",
		"mail_rep_conv" => "$mail_rep_conv"
	);
	

	return $valeurs;

}


################# VERIFIER

function formulaires_profil_verifier (){
	$id_auteur = $GLOBALS["auteur_session"]["id_auteur"];
	$errors = Array();


	$query = sql_select("*", "spip_auteurs", "id_auteur=$id_auteur");
	if ($row = sql_fetch($query)) {
		$nom_ancien = $row["nom"];
		$lang_ancien = $row["lang"];
		$bio_ancien = $row["bio"];
		$couleur_ancien = $row["couleur"];
		$url_site_ancien = $row["url_site"];
		$copyright_ancien = $row["copyright"];
	}


	$nom = _request("nom");
	$lang = _request("lang");
	$bio = _request("bio");
	$couleur = _request("couleur");
	$couleur = str_replace("#", "", $couleur);
	$copyright = _request("copyright");
	$mail_nouv_billet = _request("mail_nouv_billet");
	$mail_rep_moi = _request("mail_rep_moi");
	$mail_rep_billet = _request("mail_rep_billet");
	$mail_rep_conv = _request("mail_rep_conv");
	
	$url_site = _request("url_site");
	
	$nom = strip_tags($nom);
	$bio = strip_tags($bio);

	sql_updateq("spip_auteurs",
		array (
			"nom" => $nom,
			"lang" => $lang,
			"bio" => $bio,
			"couleur" => $couleur,
			"url_site" => $url_site,
			"copyright" => $copyright,
			"mail_nouv_billet" => $mail_nouv_billet,
			"mail_rep_moi" => $mail_rep_moi,
			"mail_rep_billet" => $mail_rep_billet,
			"mail_rep_conv" => $mail_rep_conv
		),
		"id_auteur=$id_auteur"
	);

	if ($lang != $lang_ancien) {
		include_spip("inc/session");
		actualiser_sessions($GLOBALS["auteur_session"]);
		supprimer_microcache($id_auteur, "inc/head_langue");
	}

	if ($nom != $nom_ancien OR $bio != $bio_ancien OR $url_site != $url_site_ancien) {
		supprimer_microcache($id_auteur, "noisettes/entete_auteur");
		supprimer_microcache($id_auteur, "noisettes/entete_auteur_message");
		supprimer_microcache($id_auteur, "noisettes/head_auteur");
	}
	
	if ($nom != $nom_ancien OR $copyright != $copyright_ancien ) {
			nettoyer_nom_auteur($id_auteur);
	}
	
	if ($couleur != $couleur_ancien) {
			supprimer_microcache($id_auteur, "noisettes/head_auteur");
			supprimer_microcache($id_auteur, "noisettes/css_auteur");
			supprimer_microcache($id_auteur, "noisettes/head_auteur_message");
			supprimer_microcache($id_auteur, "noisettes/head_message");
			supprimer_microcache($id_auteur, "noisettes/entete_auteur");
			supprimer_microcache($id_auteur, "noisettes/entete_auteur_message");
			supprimer_microcache($id_auteur, "noisettes/adsense_auteur");

			nettoyer_graphisme_auteur($id_auteur);
			nettoyer_logo_auteur($id_auteur);

	}


	$nom_bandeau = $_FILES['image_bandeau']['name'];
	if (strlen($nom_bandeau) > 0) {
		if (!preg_match(",\.(jpe?g|png|gif)$,i", $nom_bandeau))	$errors["image_bandeau"] = "Mauvais format.";
		else {
			include_spip("inc/filtres_images") ;
			$size = getimagesize($_FILES['image_bandeau']['tmp_name']);
			
			$largeur = $size[0];
			$hauteur = $size[1];
			$type = $size[2];
						
			if ($type == IMG_JPG) $term = ".jpg";
			else if ($type == 3) $term = ".png";
			else if ($type == IMG_GIF) $term = ".gif";
			
			if ($f = fichier_bandeau($id_auteur)) @unlink($f);
			
			nettoyer_graphisme_auteur($id_auteur);
			
			@copy($_FILES['image_bandeau']['tmp_name'], racine_bandeau($id_auteur).$term);
		}

	}
	$supprimer_bandeau = _request("supprimer_bandeau");
	if ($supprimer_bandeau) {
		$fichier = fichier_bandeau($id_auteur, false);
		@unlink($fichier);
		nettoyer_graphisme_auteur($id_auteur);
	}
	
	$nom_fond = $_FILES['image_fond']['name'];
	if (strlen($nom_fond) > 0) {
		if (!preg_match(",\.(jpe?g|png|gif)$,i", $nom_fond))	$errors["image_fond"] = "Mauvais format.";
		else {
			include_spip("inc/filtres_images") ;
			$size = getimagesize($_FILES['image_fond']['tmp_name']);
			
			$largeur = $size[0];
			$hauteur = $size[1];
			$type = $size[2];
			
			if ($type == IMG_JPG) $term = ".jpg";
			else if ($type == 3) $term = ".png";
			else if ($type == IMG_GIF) $term = ".gif";
			
			if ($f = fichier_fond($id_auteur)) @unlink($f);
			
			
			@copy($_FILES['image_fond']['tmp_name'], racine_fond($id_auteur).$term);
			nettoyer_graphisme_auteur($id_auteur);
		}

	}

	$supprimer_fond = _request("supprimer_fond");
	if ($supprimer_fond) {
		$fichier = fichier_fond($id_auteur, false);
		@unlink($fichier);
		nettoyer_graphisme_auteur($id_auteur);
	}

	$nom_logo = $_FILES['image_logo']['name'];
	if (strlen($nom_logo) > 0) {
		if (!preg_match(",\.(jpe?g|png|gif)$,i", $nom_logo))	$errors["image_logo"] = "Mauvais format.";
		else {
			include_spip("inc/filtres_images") ;
			$size = getimagesize($_FILES['image_logo']['tmp_name']);
			
			$largeur = $size[0];
			$hauteur = $size[1];
			$type = $size[2];
			
			if ($type == IMG_JPG) $term = ".jpg";
			else if ($type == 3) $term = ".png";
			else if ($type == IMG_GIF) $term = ".gif";
			
			if ($f = fichier_logo_auteur($id_auteur)) @unlink($f);
			
			
			@copy($_FILES['image_logo']['tmp_name'], _NOM_PERMANENTS_ACCESSIBLES."auton$id_auteur".$term);
			nettoyer_graphisme_auteur($id_auteur);
			nettoyer_logo_auteur($id_auteur);
		}

	}

	$supprimer_logo = _request("supprimer_logo");
	if ($supprimer_logo) {
		$fichier = fichier_logo_auteur($id_auteur, false);
		@unlink($fichier);
		nettoyer_graphisme_auteur($id_auteur);
			nettoyer_logo_auteur($id_auteur);
	}
	
	


	return $errors;
}


################### TRAITER

function formulaires_profil_traiter (){
	$id_auteur = $GLOBALS["auteur_session"]["id_auteur"];
	

}


?>