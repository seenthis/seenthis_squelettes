<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

defined('_HOST')||define('_HOST', $_SERVER['HTTP_HOST']);
defined('_STATIC_HOST')||define('_STATIC_HOST', _HOST);

include_spip("php/traduire_texte");
include_spip('inc/seenthis_data');

function unichr($u) {
	return html_entity_decode('&#x' . intval($u) . ';', ENT_NOQUOTES, "UTF-8");
    return mb_convert_encoding('&#x' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
}


function decodeUchar ($text) {
	return preg_replace('/%u([a-fA-F0-9]{4})/e', "unichr('\\1')", $text);
}



function share_tw_url($id_me) {
	$me = "http://"._SHORT_HOST."/".base_convert($id_me, 10,36);
	return $me;
}

function share_tw_texte ($texte, $l=0) {
	$texte = preg_replace(",([\t\r\n\ ]+),", " ", $texte);

	$texte = mb_substr($texte, 0, 140-$l, "utf-8");
	$pos = mb_strrpos($texte, " ", "utf-8");
	
	if ($pos > 40) {
		$texte = mb_substr($texte, 0, $pos, "utf-8")."...";
	}
	
	return $texte;
}



function calculer_enfants_syndic($id_syndic, $url_racine="", $afficher_url="", $ret="") {
	
	$ret[] = $id_syndic;
//	if (!$afficher_url) $afficher_url = $url_racine;
	
		
	
	$query = sql_select("*", "spip_syndic", "id_parent=$id_syndic");
	$total = sql_count($query);
	
	if ($afficher_url) {
		include_spip('inc/urls');
		$u = generer_url_entite($id_syndic,'site');
		if ($total > 0) $GLOBALS["afficher_enfants_syndic"] .= "<li><span class='lien_lien'><span class='lien_lien_total'><a href='$u'>►</a></span><a href='$u'><strong>$afficher_url</strong></a></span>";
		else  $GLOBALS["afficher_enfants_syndic"] .= "<li><span class='lien_lien'><span class='lien_lien_total'><a href='$u'>►</a></span><a href='$u'>$afficher_url</a></span>";
	}

	if ($total > 0) {
		$GLOBALS["afficher_enfants_syndic"] .= "<ul>";
		while ($row = sql_fetch($query)) {
			$url_site = $row["url_site"];
			$id_enfant = $row["id_syndic"];
			
			$afficher_url = substr($url_site, strlen($url_racine), 10000);
			
			
			$ret = calculer_enfants_syndic($id_enfant, $url_site, $afficher_url, $ret);
			
		}
		$GLOBALS["afficher_enfants_syndic"] .= "</ul>";
	}
	$GLOBALS["afficher_enfants_syndic"] .= "</li>";
	
	return $ret;
}

function afficher_enfants_syndic($rien) {
	return $GLOBALS["afficher_enfants_syndic"];
}


function share_tw($id_me, $texte) {
	
	$me = share_tw_url($id_me);
	$l = strlen($me) + 4;
	
	$texte = share_tw_texte ($texte, $l);
	
	$texte .= " ".$me;

	return $texte;
}


function share_fb($id_me, $texte) {


	$texte = preg_replace(",([\t\r\n\ ]+),", " ", $texte);
	
	$me = "http://"._HOST."/messages/$id_me";
	
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


function mot_chemin($rien) {

	$url = parse_url($_SERVER["REQUEST_URI"]);
	$url = $url["path"];
	$url = substr($url, strrpos($url, "/")+1, 1000);	
	
	return $url;
}

function share_lien($id_me) {

	
	$me = "http://"._HOST."/messages/$id_me";
	
	$me = rawurlencode($me);
	
	return $me;

}

function filtrer_rediriger_images($reg) {
	//return $reg[0];
	$lien = $reg[1];
	
	if ( ! preg_match(",^http,", $lien)) {
		$code = substr(md5($lien), 0, 1);
		$code = hexdec($code) % 4;		
		$lien = "http://".str_replace('%s', $code, _STATIC_HOST).'/'.$lien;
	}
	return " src='$lien'" ;
}
function filtrer_rediriger_css($reg) {
	$lien = $reg[2];
	$media = $reg[1];
	
	if ( ! preg_match(",^http,", $lien)) {
		$code = substr(md5($lien), 0, 1);
		$code = hexdec($code) % 4;		
		$lien = "http://".str_replace('%s', $code, _STATIC_HOST).'/'.$lien;
	}
	return "<link rel='stylesheet'  media='$media' href='$lien'" ;
}

function filtrer_rediriger_background($reg) {
	$lien = $reg[1];
	
	if ( ! preg_match(",^http,", $lien)) {
		$code = substr(md5($lien), 0, 1);
		$code = hexdec($code) % 4;
		$lien = "http://".str_replace('%s', $code, _STATIC_HOST).'/'.$lien;
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
//	return $texte;
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

function afficher_miniature($img, $max = 200) {

	if (preg_match(',\.svg$,i', $img)) {
		if (defined('_SVG2PNG_SERVER')) {
			$cvt = parametre_url(_SVG2PNG_SERVER,'url',$img);
			$box = " target='_blank'";
		} else {
			return false;
		}
	} else {
		$cvt = $img;
		$box = " rel='shadowbox[Portfolio]'";
	}

	if (!$vignette = copie_locale($cvt, 'test')
	AND $id_me = _request('id_me')) {
		# a noter : ce id_me est le numero du message qu'on cree OU DU PARENT
		include_spip('inc/acces');
		$i = $GLOBALS['visiteur_session']['id_auteur'];
		$sec = afficher_low_sec($i, "miniature $max $cvt $id_me");
		$url = generer_url_action('creer_miniature');
		$url = parametre_url($url, 'id_auteur', $i);
		$url = parametre_url($url, 'id_me', $id_me);
		$url = parametre_url($url, 'img', $cvt);
		$url = parametre_url($url, 'max', $max);
		$url = parametre_url($url, 'sec', $sec);
		return "<div style=\"max-width:".$max."px; min-height:30px; background-image: url(".find_in_path('imgs/image-loading.gif')."); background-repeat: no-repeat;\"><a href='$img' class='display_box'$box><img src='$url' alt=\"". attribut_html($img)."\" style=\"max-width:${max}px;\" /></a></div>";
	}

	list($width, $height) = @getimagesize($vignette);

	if (($width * $height) < 300) return;
	
	
	include_spip("inc/filtres_images_mini");
	$vignetter = image_reduire($vignette, 300, 180);
	
	if ($vignetter == $vignette) return;
	
	$vignette = inserer_attribut($vignetter, "alt", "");

	if ($width <= $max && $height <= $max)
		return $vignette;

	return "<a href='$img' class='display_box'$box>$vignette</a>";
}

$GLOBALS["oc_lies"] = array();

function reset_oc_lies($rien) {
	$GLOBALS["oc_lies"] = array();
}

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


function stocker_id_me($id_me) {
	$GLOBALS["liste_id_me"][$id_me] = $id_me;
}

function retour_id_me($rien) {
	return $GLOBALS["liste_id_me"];
}

function stocker_id_me_date($id_me, $date) {
	if (!$GLOBALS["liste_id_me"][$id_me]) $GLOBALS["liste_id_me"][$id_me] = $date;
}

function retour_id_me_date($rien) {
	if ($ret = $GLOBALS["liste_id_me"]) {
	
		// Un peu complexe, car plusieurs messages peuvent avoir exactement la meme date
		
		// 1. On partout le tableau pour refaire une liste inversée, avec potentiellement plusieurs id_me par date
		foreach($ret as $id=>$date) {
			$liste[$date][] = $id;
		}
		// 2. On inverse le tableau selon la date
		krsort($liste);
		
		// 3. On recolle les id_me 
		foreach($liste as $date => $arr) {
			foreach ($arr as $val) {
				$l[] = $val;
			}
		}
		
		return($l);
	} else return 0;
}

function stocker_rel($id_mot, $lien, $off) {
	$GLOBALS["oc_rel"]["$id_mot"] = "mot$id_mot-$lien";
	if ($off == "oui") $GLOBALS["oc_off"]["$id_mot"] = "off";
}

function afficher_rel_mot($id_mot) {
	return $GLOBALS["oc_rel"]["$id_mot"];
}

function afficher_off_mot($id_mot) {
	return $GLOBALS["oc_off"]["$id_mot"];
}

$GLOBALS["mots_lies"] = array();
$GLOBALS["mots_lies_titre"] = array();
function compter_mots_lies($id_mot) {
	$GLOBALS["mots_lies"]["$id_mot"] ++;
}
function compter_mots_titre ($id_mot, $titre) {
	$titre = str_replace( "_", " ", $titre);
	$GLOBALS["mots_lies_titre"]["$id_mot"] = $titre;
	//echo "$id_mot - $titre";
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


function retour_mots_lies_wordle($rien) {
	arsort($GLOBALS["mots_lies"]);
	foreach($GLOBALS["mots_lies"] as $id_mot => $k) {
		if ($k > 1 && strlen(trim($GLOBALS["mots_lies_titre"]["$id_mot"]))>0) {
			$ret .= $GLOBALS["mots_lies_titre"]["$id_mot"].":$k\n";
		}
	}
	return $ret;
}

$GLOBALS["stocker_type"] = array();
function stocker_type($id, $type) {
	$GLOBALS["stocker_type"]["$type"][] = $id;
}
function sortir_type($rem, $type) {
	return $GLOBALS["stocker_type"]["$type"];
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
	
	$date = date("Y-m-d H:i:s",$timeStamp);
	$stocker_date["$age"] = $date;
	
	return $date;
	
}

function afficher_cc($cc) {
	if (preg_match("/^BY/", $cc)) {
		$lien = strtolower($cc);
		
		
		return "<a href='http://creativecommons.org/licenses/$lien/3.0/' class='spip_out by_cc'>CC $cc</a>";
	}
	else if ($cc == "CC0") {
		$lien = strtolower($cc);
		return "<a href='http://creativecommons.org/publicdomain/zero/1.0/' class='spip_out by_cc by_zero'>PUBLIC DOMAIN</a>";
	}
	else if ($cc == "LAL") {
		$lien = strtolower($cc);
		return "<a href='http://artlibre.org/licence/lal' class='spip_out by_cc by_lal'>ART LIBRE</a>";
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
		if (preg_match("/^fr/", $langue) > 0) $choix = "fr";
		if (preg_match("/^en/", $langue) > 0) $choix = "en";
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


function critere_follow_sites($idb, &$boucles, $crit) {
	$boucle = &$boucles[$idb];
	$quoi = calculer_liste($crit->param[0], array(), $boucles, $boucles[$idb]->id_parent);

	$env_follow = (calculer_argument_precedent($idb, 'follow', $boucles));

	$boucle->where[] = 'liste_me_follow_sites('.$quoi.', '.$env_follow.')';

}

function precurseurs($mot) {
	$l = mb_strlen($mot,'UTF-8');
	$a = array();

	for($i=1; $i<$l; $i++)
		$a[] = mb_substr($mot,0,$i,'UTF-8');

	return $a;
}


function elaguer_arbre_successeurs($x) {
	$l = array();
	foreach($x as $k => $xx) {
		if (empty($xx))
			$l[] = $k;
		else {
			if (count($xx) > 4)
				$l[] = $k;
			else
			foreach(elaguer_arbre_successeurs($xx) as $a) {
				$l[] = $k.$a;
			}
		}
	}
	return $l;
}

function successeurs($mot) {
	if (strlen($mot) < 2) return array();

	$motq = str_replace(array('_','%',"'"), array('\\_', '\\%','\\\''), $mot);

	$a = array();
	$d = mb_strlen($mot);

	$s = sql_query("SELECT DISTINCT(tag)
	FROM spip_me_tags
	WHERE class='#' AND tag LIKE '${motq}_%'
	ORDER BY CHAR_LENGTH(tag)
	LIMIT 200");
	$tous = array();

	while ($m = sql_fetch($s)) {
		$tag = $m['tag'];
		$l = mb_strlen($tag);
		$h = &$tous;
		for ($i=$d; $i<$l; $i++) {
			$c = mb_strtolower(mb_substr($tag,$i,1, 'UTF-8'), 'UTF-8');
			if (!isset($h[$c]))
				$h[$c] = array();
			$h = &$h[$c];
		}
	}

	$tous = elaguer_arbre_successeurs($tous);
	foreach ($tous as &$t)
		$t = proteger_amp(substr($mot,1).$t);

	return $tous;
}

function liste_me_follow_sites($quoi, $env_follow) {
	$me = $GLOBALS['visiteur_session']['id_auteur'];
	if ($me > 0) {
	
	 	$sites = array_map('array_pop', sql_allfetsel('id_syndic', 'spip_me_follow_url', 'id_follow='.$me));
	 	
	 	$parents = $sites;
	 	while ( $enfants = array_map('array_pop', sql_allfetsel('id_syndic', 'spip_syndic', sql_in('id_parent', $parents))) ) {
	 		$parents = $enfants;
		 	$sites = array_merge($sites, $enfants);
	 	}
		return '('.sql_in('id_syndic', $sites).')';
	}
}



// follow implique #SESSION
function critere_follow_dist($idb, &$boucles, $crit) {
	$boucle = &$boucles[$idb];
	$quoi = calculer_liste($crit->param[0], array(), $boucles, $boucles[$idb]->id_parent);

	$env_follow = (calculer_argument_precedent($idb, 'follow', $boucles));

	$boucle->where[] = 'liste_me_follow('.$quoi.', '.$env_follow.')';
}

//
// critere {follow} ou {follow xxx}
// {follow} ou {follow follow} :
// - mes messages,
// - mes favoris
// - les msgs des auteurs que je suis
// - les favoris des auteurs que je suis
// {follow me} : mes messages + mes favoris
// {follow all} : tous les messages de la base
// {follow LOGIN} : tous les messages de ce login + ses favoris
// [A noter les login "me", "follow" et "all" sont niques]
function liste_me_follow($quoi, $env_follow) {

	// si le mode n'est pas precisé explicitement dans le critere,
	// se baser sur l'env
	if (!$quoi) $quoi = $env_follow;
	$me = $GLOBALS['visiteur_session']['id_auteur'];

	// critère {follow #ID_AUTEUR}
	if (is_numeric($quoi)) {
		$val = floor($quoi);
		if ($val > 0) {
			$me = $val;
			$quoi = "me";
		}
	}

	switch ($quoi) {
		case 'all':
			return;
		case 'me':
			if ($me > 0) {
				return '(id_auteur='.$me.' OR '.sql_in('id_me', array_map('array_pop', sql_allfetsel('id_me', 'spip_me_share', 'id_auteur='.$me))).')';
			} else
				return '0=1';
		case 'follow':
		case '':
			$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
			if ($id_auteur > 0) {
				$suivi = liste_follow($id_auteur);
				$suivi[] = $id_auteur;

				# optimisation:
				# ne retenir que les auteurs ayant poste au moins un message
				$suivi = array_map('array_pop',
					sql_allfetsel('DISTINCT(id_auteur)',
						'spip_me', 'statut="publi" AND '.sql_in('id_auteur',$suivi)
					)
				);

				$auteurs = sql_in('id_auteur',$suivi);

				return '('.$auteurs
					.' OR '.sql_in('id_me',
						array_map('array_pop', sql_allfetsel('id_me', 'spip_me_share', $auteurs))
					).')';
			} else
				return '0=1';
		default:
			if ($auteur = sql_fetsel('id_auteur', 'spip_auteurs', 'login='.sql_quote($quoi))) {
				$me = $auteur['id_auteur'];
				return '(id_auteur='.$me.' OR '.sql_in('id_me', array_map('array_pop', sql_allfetsel('id_me', 'spip_me_share', 'id_auteur='.$me))).')';
			} else
				return '0=1';
	}
}

// liste des auteurs que je follow ;
// avec un static car ca peut revenir souvent sur une meme page
function liste_follow($id_auteur) {
	static $cache = array();
	
	if (!isset($cache[$id_auteur])) {
		$suivi = sql_allfetsel('id_auteur', 'spip_me_follow', 'id_follow='.$id_auteur);
		if (is_array($suivi))
			$suivi = array_map('array_pop', $suivi);
		else
			$suivi = array();
		$cache[$id_auteur] = $suivi;
	}

	return $cache[$id_auteur];
}

// stocker une chaine dans un CDATA
// a noter qu'il faut "echapper" un eventuel "]]>"
function filtre_cdata($t) {
	if (preg_match(',[&<>],', $t))
		return "<![CDATA[" . str_replace(']]>', ']]]]><![CDATA[>', $t).']]>';
	else
		return $t;
}

?>