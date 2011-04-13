<?php

include_spip("php/traduire_texte");

function unichr($u) {
	return html_entity_decode('&#x' . intval($u) . ';', ENT_NOQUOTES, "UTF-8");
    return mb_convert_encoding('&#x' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
}


function decodeUchar ($text) {
	return preg_replace('/%u([a-fA-F0-9]{4})/e', "unichr('\\1')", $text);
}



function share_tw($id_me, $texte) {
	$texte = preg_replace(",([\t\r\n\ ]+),", " ", $texte);
	
	$me = "http://seen.li/".base_convert($id_me, 10,36);
	
	$l = strlen($me) + 4;
	
	$texte = mb_substr($texte, 0, 140-$l, "utf-8");
	$pos = mb_strrpos($texte, " ", "utf-8");
	
	if ($pos > 40) {
		$texte = mb_substr($texte, 0, $pos, "utf-8")."...";
	}
	
	$texte .= " ".$me;

	return $texte;
}


function share_fb($id_me, $texte) {


	$texte = preg_replace(",([\t\r\n\ ]+),", " ", $texte);
	
	$me = "http://seenthis.net/messages/$id_me";
	
	$l = strlen($me) + 4;
	
	$texte = mb_substr($texte, 0, 250-$l, "utf-8");
	$pos = mb_strrpos($texte, " ", "utf-8");
	
	if ($pos > 40) {
		$texte = mb_substr($texte, 0, $pos, "utf-8")."...";
	}
	
	$texte = rawurlencode($texte);
	$me = rawurlencode($me);
	
	return "http://www.facebook.com/sharer.php?u=$me&amp;t=$texte";

}

function share_lien($id_me) {

	
	$me = "http://seenthis.net/messages/$id_me";
	
	$me = rawurlencode($me);
	
	return $me;

}

function filtrer_rediriger_images($reg) {
	$lien = $reg[1];
	
	if ( ! preg_match(",^http,", $lien)) {
		$code = substr(md5($lien), 0, 1);
		$code = hexdec($code) % 4;		
		$lien = "http://static$code.seenthis.net/$lien";
	}
	return " src='$lien'" ;
}
function filtrer_rediriger_css($reg) {
	$lien = $reg[2];
	$media = $reg[1];
	
	if ( ! preg_match(",^http,", $lien)) {
		$code = substr(md5($lien), 0, 1);
		$code = hexdec($code) % 4;		
		$lien = "http://static$code.seenthis.net/$lien";
	}
	return "<link rel='stylesheet'  media='$media' href='$lien'" ;
}

function filtrer_rediriger_background($reg) {
	$lien = $reg[1];
	
	if ( ! preg_match(",^http,", $lien)) {
		$code = substr(md5($lien), 0, 1);
		$code = hexdec($code) % 4;		
		$lien = "http://static$code.seenthis.net/$lien";
	}
	return "url($lien)" ;
}

function filtrer_background_css($flux) {

	$flux = preg_replace_callback(",url\((.*)\),", "filtrer_rediriger_background", $flux );

	return $flux;
}

function filtrer_images_page($flux) {
	if ($_SERVER["HTTP_HOST"] == "localhost:8888") return $flux;
	
	$flux = preg_replace_callback(",[[:space:]]src=[\"\']([^\"\']*)[\"\'],", "filtrer_rediriger_images", $flux );
	$flux = preg_replace_callback(",<link[[:space:]]+rel='stylesheet'[[:space:]]+media='(.*)'[[:space:]]+href='(.*)',", "filtrer_rediriger_css", $flux );
	$flux = filtrer_background_css($flux);
	return $flux;
}

function mini_html($texte) {
	$texte = filtrer_images_page($texte);
	$texte = preg_replace(",\n[\t\ ]*,", "\n", $texte);
	$texte = preg_replace(",\n+,", "\n", $texte);
	return $texte;
}



function logo_auteur_vide ($couleur, $taille) {
	include_spip("inc/filtres_images");
	$img = "squelettes/imgs/logo-auteur.png";
	
	$couleur = couleur_luminance($couleur, 0.57);
	
	$img = image_sepia($img, $couleur);
	$img = image_graver($img);
	$img = image_reduire($img, $taille);
	$img = image_aplatir($img, "gif");
	$img = extraire_attribut($img, "src");
	return $img;
}

function couleur_chroma ($coul, $num) {
	include_spip("inc/filtres_images");

	$pos = substr($num, 0, strpos($num, "/")) -  1;
	$tot = substr($num, strpos($num, "/")+1, strlen($num));
	
	$couleurs = couleur_hex_to_dec($coul);
	$r= $couleurs["red"];
	$g= $couleurs["green"];
	$b= $couleurs["blue"];

	$hsv = couleur_rgb2hsv($r,$g,$b);
	$h = $hsv["h"];
	$s = $hsv["s"];
	$v = $hsv["v"];
	
	$h = $h + (1/$tot)*$pos;
	if ($h > 1) $h = $h - 1;
					
	$rgb = couleur_hsv2rgb($h,$s,$v);
	$r = $rgb["r"];
	$g = $rgb["g"];
	$b = $rgb["b"];
	
	$couleurs = couleur_dec_to_hex($r, $g, $b);
	
	return $couleurs;
}

function afficher_miniature($img) {
	list($width, $height) = @getimagesize($img);
	
	if (($width * $height) < 300) return;
	
	$max = 240;
	
	if ($width <= $max && $height <= $max) return "<img src='$img' alt='' width='$width' height='$height'  />";

	$rapport = $max / max($width, $height);
	
	$width = floor($width * $rapport);
	$height = floor($height * $rapport);
	 return "<a rel='shadowbox[Portfolio]' href='$img' class='display_box'><img src='$img' alt='' width='$width' height='$height'  /></a>";
}


$GLOBALS["oc_lies"] = array();
function compter_oc_lies($id_mot, $relevance) {
	if ($relevance > 300 && $relevance > $GLOBALS["oc_lies"]["$id_mot"]) $GLOBALS["oc_lies"]["$id_mot"] = $relevance;	
}

function retour_oc_lies($rien) {
	arsort($GLOBALS["oc_lies"]);
	foreach($GLOBALS["oc_lies"] as $id_mot => $k) {
		if ($k > 1) {
			$ret[] = $id_mot;
		}
	}
	return $ret;
}


$GLOBALS["mots_lies"] = array();
function compter_mots_lies($id_mot) {
	$GLOBALS["mots_lies"]["$id_mot"] ++;
}


function retour_mots_lies($rien) {
	arsort($GLOBALS["mots_lies"]);
	foreach($GLOBALS["mots_lies"] as $id_mot => $k) {
		if ($k > 1) {
			$ret[] = $id_mot;
		}
	}
	return $ret;
}


$GLOBALS["compter_auteurs"] = array();
function compter_auteurs($id_auteur) {
	$GLOBALS["compter_auteurs"]["$id_auteur"] ++;
}

function retour_compteur_auteurs($rien) {
	arsort($GLOBALS["compter_auteurs"]);
	foreach($GLOBALS["compter_auteurs"] as $id_auteur => $k) {
		if ($k > 0) {
			$ret[] = $id_auteur;
		}
	}
	
	$GLOBALS["compter_auteurs"] = array();
	return $ret;
}

function stocker_auteur($id_auteur, $troll, $total) {
	if ($total > 30) $total = 30;
		
	$res = round($troll * ((100 + 3*$total)  / 100));
	
	if ($total < 3) $res = ceil($res / 2);
	
	$res ++;
	$GLOBALS["compter_auteurs"]["$id_auteur"] = $res;
}

function decaler_date ($age) {

	global $stocker_date;
	if ($stocker_date["$age"]) return $stocker_date["$age"];
	


	$now = date("U");

	$thePHPDate = getdate($now);
	$thePHPDate['mday'] = $thePHPDate['mday'] - $age;
	$timeStamp = mktime($thePHPDate['hours'], $thePHPDate['minutes'], $thePHPDate['seconds'], $thePHPDate['mon'], $thePHPDate['mday'], $thePHPDate['year']);
	
	$date = date("Y-m-d",$timeStamp);
	$stocker_date["$age"] = $date;
	
	return $date;
	
}

function afficher_cc($cc) {
	if (preg_match("/^BY/", $cc)) {
		$lien = strtolower($cc);
		
		
		return "<a href='http://creativecommons.org/licenses/$lien/3.0/' class='spip_out by_cc'>CC $cc</a>";
	}
}


function langue_visiteur($id_auteur) {
	$query = sql_select("lang", "spip_auteurs", "id_auteur=$id_auteur");
	if ($row = sql_fetch($query)) {
		$lang = $row["lang"];
	}

	// si pas de langue stockée, détecter et stocker!
	if (strlen($lang) < 2) {
		$lang = detecter_langue_visiteur(0);
		sql_updateq("spip_auteurs", 
			array("lang" => $lang),
			"id_auteur=$id_auteur"
		);
	}

	return $lang;	
}
function detecter_langue_visiteur($rien) {

	if (isset($HTTP_ACCEPT_LANGUAGE)) $langues = $HTTP_ACCEPT_LANGUAGE;
	else if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) $langues = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
	else return;
	
	if (strlen ($langues) < 1) return;
	
	$langues = explode(",", $langues);
	
	$i = 0;
	$choix = false;
	
	$choix = false;
	
	while ($i < count($langues) && !$choix) {
	
		$langue = $langues[$i];	
		if (preg_match("/fr/", $langue) > 0) $choix = "fr";
		if (preg_match("/en/", $langue) > 0) $choix = "en";
//		if (preg_match("/it/", $langue) > 0) $choix = "it";
//		if (preg_match("/ar/", $langue) > 0) $choix = "ar";
//		if (preg_match("/es/", $langue) > 0) $choix = "es";
//		if (preg_match("/de/", $langue) > 0) $choix = "de";
			
		$i++;
	}
	
	// Par défaut: angliche
	if (!$choix) $choix = "en";
	
	if ($choix) return $choix;
}




?>