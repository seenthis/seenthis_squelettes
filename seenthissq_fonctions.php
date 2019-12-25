<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

defined('_HOST')||define('_HOST', $_SERVER['HTTP_HOST']);
defined('_STATIC_HOST')||define('_STATIC_HOST', _HOST);

if (!defined('_SEENTHIS_REG_SPAM')) {
	define('_SEENTHIS_REG_SPAM', "case|.*doll.*|pharmacy|laser|.*astrolo.*|iphone|batter.*|vpn|wedding|marriage|manager|management|seo|weed|dating|insurance|marketing|cleaning|vashikaran|jammer");
}

include_spip("php/traduire_texte");
include_spip('inc/seenthis_data');

function unichr($u) {
	return html_entity_decode('&#x' . intval($u) . ';', ENT_NOQUOTES, "UTF-8");
}


function decodeUchar ($text) {
	return preg_replace('/%u([a-fA-F0-9]{4})/e', "unichr('\\1')", $text);
}


function share_tw_url($id_me) {
	$me = _HTTPS."://"._SHORT_HOST."/".base_convert($id_me, 10,36);
	return $me;
}

function calculer_enfants_syndic($id_syndic, $url_racine = '', $afficher_url = '', $ret = array()) {
	
	$ret[] = $id_syndic;
	
	// si on a la même url en http & https, ajouter le doublon au tableau de retour
	$lien_flou = preg_replace(',/$,', '', preg_replace(',^(https?://)?,i', '', $url_racine));
	if ($doublon = sql_getfetsel('id_syndic', 'spip_syndic', "id_syndic != $id_syndic AND url_site LIKE ".sql_quote('%' . $lien_flou))) {
		$ret[] = $doublon;
	}
	
	$query = sql_select("*", "spip_syndic", "id_parent=$id_syndic", /* group by */ '', /* order by*/ '', /* limit */ '0,100');
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

function mot_chemin($rien) {
	if (_request('recherche')) return _request('recherche'); // si on a ?recherche=xxx, chercher xxx
	$url = parse_url($_SERVER["REQUEST_URI"]);
	$url = $url["path"];
	$url = substr($url, strrpos($url, "/")+1, 1000);
	return $url;
}


function filtrer_rediriger_images($reg) {
	//return $reg[0];
	$lien = $reg[1];
	
	if ( ! preg_match(",^http,", $lien)) {
		$code = substr(md5($lien), 0, 1);
		$code = hexdec($code) % 4;		
		$lien = _HTTPS."://".str_replace('%s', $code, _STATIC_HOST).'/'.$lien;
	}
	return " src='$lien'" ;
}
function filtrer_rediriger_css($reg) {


	$lien = $reg[2];
	$media = $reg[1];
	
	$lien_ar = direction_css($lien, "rtl");

	if ( ! preg_match(",^http,", $lien)) {
		$code = substr(md5($lien), 0, 1);
		$code = hexdec($code) % 4;		
		$lien = _HTTPS."://".str_replace('%s', $code, _STATIC_HOST).'/'.$lien;

		if ( ! preg_match(",^http,", $lien_ar)) {
			$code = substr(md5($lien_ar), 0, 1);
			$code = hexdec($code) % 4;		
			$lien_ar = _HTTPS."://".str_replace('%s', $code, _STATIC_HOST).'/'.$lien_ar;
			
			// De cette façon, ne créer l'alternative RTL qu'une fois
			$ret = "<link rel='alternate stylesheet'  media='$media' href='$lien_ar' type='text/css' id='css_rtl'>\n" ;
		}
	}
	$ret .= "<link rel='stylesheet'  media='$media' href='$lien' type='text/css' id='css_default'>\n" ;
	return $ret;
}

function filtrer_rediriger_background($reg) {
	$lien = $reg[1];
	
	if ( ! preg_match(",^http|data,", $lien)) {
		$code = substr(md5($lien), 0, 1);
		$code = hexdec($code) % 4;
		$lien = _HTTPS."://".str_replace('%s', $code, _STATIC_HOST).'/'.$lien;
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
	$flux = preg_replace_callback(",<link[[:space:]]+rel='stylesheet'[[:space:]]+media='(.*)'[[:space:]]+href='([^\']*)'.*>,", "filtrer_rediriger_css", $flux );
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
	include_spip("filtres/couleurs");
	include_spip("filtres/images_transforme");
	$img = find_in_path("imgs/logo-auteur.png");
	
	$couleur = couleur_luminance($couleur, 0.57);
	
	$img = image_sepia($img, $couleur);
	$img = image_graver($img);
	$img = image_reduire($img, $taille);
	$img = image_aplatir($img, "gif");
	$img = extraire_attribut($img, "src");
	return $img;
}

function couleur_chroma ($coul, $num) {
	include_spip("filtres/images_lib");

	$pos = substr($num, 0, strpos($num, "/")) -  1;
	$tot = substr($num, strpos($num, "/")+1, strlen($num));
	
	$couleurs = _couleur_hex_to_dec($coul);
	$r= $couleurs["red"];
	$g= $couleurs["green"];
	$b= $couleurs["blue"];

	$hsv = _couleur_rgb2hsv($r,$g,$b);
	$h = $hsv["h"];
	$s = $hsv["s"];
	$v = $hsv["v"];
	
	$h = $h + (1/$tot)*$pos;
	if ($h > 1) $h = $h - 1;
					
	$rgb = _couleur_hsv2rgb($h,$s,$v);
	$r = $rgb["r"];
	$g = $rgb["g"];
	$b = $rgb["b"];
	
	$couleurs = _couleur_dec_to_hex($r, $g, $b);
	
	return $couleurs;
}

function copie_locale_safe($source, $mode='auto') {
	if (!copie_locale($source, 'test')
	AND $u = parametre_url($source, 'var_hasard', rand(0,10000000), '&')
	AND $a = copie_locale($u, $mode)) {
		rename($a, _DIR_RACINE.fichier_copie_locale($source));
	}

	return copie_locale($source, $mode);
}

function afficher_miniature($img, $maxw = 600, $maxh = 700) {
	include_spip('inc/distant');

	if (preg_match(',\.svg$,i', $img)) {
		if (defined('_SVG2PNG_SERVER')) {
			$cvt = parametre_url(_SVG2PNG_SERVER,'url',$img);
			$box = " target='_blank'";
		} else {
			return false;
		}
	} else {
		$cvt = $img;
		$box = "";
	}

	//
	// chargement asynchrone ?
	//
	if (!$vignette = copie_locale_safe($cvt, 'test')
	AND $GLOBALS['visiteur_session']['id_auteur'] > 0  # eviter sur l'API
	) {
		include_spip('inc/acces');
		$i = 1; #$GLOBALS['visiteur_session']['id_auteur'];
		$sec = afficher_low_sec($i, "miniature $maxw $maxh $cvt");
		$url = generer_url_action('creer_miniature');
		$url = parametre_url($url, 'id_auteur', $i);
		$url = parametre_url($url, 'img', $cvt);
		$url = parametre_url($url, 'maxw', $maxw);
		$url = parametre_url($url, 'maxh', $maxh);
		$url = parametre_url($url, 'sec', $sec, '\\x26');
		
		
		$selecteur = md5($img);

		$vignette = "<span class='$selecteur'><img src='".find_in_path('imgs/image-loading.gif')."' alt=\"". attribut_html($img)."\" style=\"max-width:${maxw}px; max-height:${maxh}px;\" />"
		."<script>\$.get('".$url."', function(data){\$('." . $selecteur . "').replaceWith(data);calculer_portfolio_ligne();})
		</script></span>";

		// $('." . $selecteur . "').load('".$url."');
		// preparer l'image pour photoswipe (mais on n'en connait pas les dimensions)

		return "$vignette";
	}

	return calculer_miniature($img, $maxw, $maxh);
}

function calculer_miniature($img, $maxw = 600, $maxh = 700) {
	include_spip('inc/distant');

	if (preg_match(',\.svg$,i', $img)) {
		if (defined('_SVG2PNG_SERVER')) {
			$cvt = parametre_url(_SVG2PNG_SERVER,'url',$img);
			$box = " target='_blank'";
		} else {
			return false;
		}
	} else {
		$cvt = $img;
		$box = "";
	}

	//
	// chargement synchrone
	//
	$vignette = copie_locale_safe($cvt);

	if ($srcsize = @getimagesize($vignette)) {
		$width = $srcsize[0];
		$height = $srcsize[1];

		/*
		if (($width * $height) < 300) {
			return;
		}
		*/

		include_spip("inc/filtres_images_mini");
		$vignetter = image_reduire($vignette, $maxw, $maxh);
		
		/*
		if ($vignetter == $vignette) {
			return $vignette;
		}
		*/

		$vignette = inserer_attribut($vignetter, "alt", "");

		// preparer l'image pour photoswipe
		$vignette = inserer_attribut($vignette, "data-photo", $img);
		$vignette = inserer_attribut($vignette, "data-photo-h", $height);
		$vignette = inserer_attribut($vignette, "data-photo-w", $width);
		$vignette = vider_attribut($vignette, "width");
		$vignette = vider_attribut($vignette, "height");
		$vignette = vider_attribut($vignette, "style");

		$prop = $height / $width * 100;

		// on veut avoir le lien (pour pouvoir "copier le lien")
		// mais faut-il toujours ouvrir l'image ? la box s'en charge
		// quand c'est nécessaire (pour zoomer)
		$onclick = " onclick='return false;'";

		return "<a$onclick href='$img' class='display_box'$box><span class='image' style='padding-bottom:$prop%'>$vignette</span></a>";
	} else {
		return '';
	}
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


$GLOBALS["mots_lies"] = array();
$GLOBALS["mots_lies_titre"] = array();
function compter_mots_lies($id_mot) {
	$GLOBALS["mots_lies"]["$id_mot"] ++;
}
function compter_mots_titre ($id_mot, $titre) {
	$titre = str_replace( "_", " ", $titre);
	$titre = preg_replace( "/^.*:/", "", $titre); # "position:Economist"
	$titre = preg_replace( "/^#/", "", $titre); # "#hashtag"
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
		return "<a href='https://creativecommons.org/licenses/$lien/3.0/' class='spip_out by_cc'>CC $cc</a>";
	}
	else if ($cc == "CC0") {
		return "<a href='https://creativecommons.org/publicdomain/zero/1.0/' class='spip_out by_cc by_zero'>PUBLIC DOMAIN</a>";
	}
	else if ($cc == "LAL") {
		return "<a href='http://artlibre.org/' class='spip_out by_cc by_lal'>ART LIBRE</a>";
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

	if (isset($HTTP_ACCEPT_LANGUAGE)) {
		$langues = $HTTP_ACCEPT_LANGUAGE;
	} else if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
		$langues = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
	} else {
		return;
	}
	
	if (strlen ($langues) < 1) {
		return;
	}
	
	$langues = explode(",", $langues);
	
	$i = 0;
	$choix = false;
	while ($i < count($langues) && !$choix) {
		$langue = $langues[$i];
		if (preg_match("/^fr_tu/", $langue) > 0) $choix = "fr_tu";
		else if (preg_match("/^fr/", $langue) > 0) $choix = "fr";
		else if (preg_match("/^en/", $langue) > 0) $choix = "en";
		else if (preg_match("/^de/", $langue) > 0) $choix = "de";
		else if (preg_match("/^ar/", $langue) > 0) $choix = "ar";
		else if (preg_match("/^es/", $langue) > 0) $choix = "es";
		else if (preg_match("/^nl/", $langue) > 0) $choix = "nl";
//		if (preg_match("/it/", $langue) > 0) $choix = "it";
//		if (preg_match("/ar/", $langue) > 0) $choix = "ar";
//		if (preg_match("/es/", $langue) > 0) $choix = "es";
//		if (preg_match("/de/", $langue) > 0) $choix = "de";
			
		$i++;
	}
	
	// Par défaut: angliche
	if (!$choix) {
		$choix = "en";
	}
	return $choix;
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

// escape pour like
function likeq($m) {
	return str_replace(array('&amp;', '_','%',"'"), array('&', '\\_', '\\%','\\\''), $m);
}

function successeurs($mot) {
	if (strlen($mot) < 2) return array();

	$a = array();
	$d = mb_strlen($mot);
	$motq = likeq($mot);

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
	$me = isset($GLOBALS['visiteur_session']['id_auteur']) ? $GLOBALS['visiteur_session']['id_auteur'] : 0;

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
			$id_auteur = isset($GLOBALS['visiteur_session']['id_auteur']) ? $GLOBALS['visiteur_session']['id_auteur'] : 0;
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
// http://www.w3.org/TR/xml/#charsets
function filtre_cdata($t) {
	if (preg_match(',[<>&\x0-\x8\xb-\xc\xe-\x1f],u', $t)) {
		$t = preg_replace_callback('/[\x0-\x8\xb-\xc\xe-\x1f]/u',
			create_function('$x','return "&#x".bin2hex(\'$x[0]\').";";'), $t);
		return "<![CDATA[" . str_replace(']]>', ']]]]><![CDATA[>', $t).']]>';
	} else
		return $t;
}

# tag/[(#TAG|replace{#}|mb_strtolower{UTF-8}|urlencode_1738_plus
function balise_URL_TAG_dist($p) {
	$_tag = champ_sql('tag', $p);
	$_class = champ_sql('class', $p);
	$p->code = "((\$class=$_class) == '#' OR (\$class==''))
		? 'tag/'.urlencode_1738_plus(mb_strtolower(str_replace('#', '', $_tag),'UTF-8'))
		: ((\$class=='oc')
		? 'tag/'.urlencode_1738_plus(mb_strtolower($_tag,'UTF-8'))
		: ((\$class=='url')
		? 'sites/'.md5($_tag)
		: ''))";
	$p->interdire_scripts = true;
	return $p;
}

function url_tag($tag) {
	if (preg_match(',^http,i', $tag))
		return 'sites/' . md5($tag);
	$tag = str_replace('#', '', $tag);
	return 'tag/'.urlencode_1738_plus(mb_strtolower($tag, 'UTF-8'));
}

function compte_twitter($id_auteur) {
	global $comptes_twitter;
	
	if ($comptes_twitter["$id_auteur"]) return $comptes_twitter["$id_auteur"];
	else {
		$query = sql_select("twitter", "spip_auteurs", "id_auteur=$id_auteur");
		if ($row = sql_fetch($query)) {
			$twitter = $row["twitter"];
			
			if (strlen($twitter) > 0) {
				if (!preg_match(",^@,", $twitter)) $twitter = "@".$twitter;
			}
			$comptes_twitter["$id_auteur"] = $twitter;
			return $twitter;
		}
	}
}

function filtre_bookmarklet($texte) {
	return preg_replace(array("/\r|\n/", '~\s~'), array('', '%20'), $texte);
}

function filtre_date_seenthis($date) {
	return journum($date) . '/' . mois($date) . '/' . annee($date);
}
?>
