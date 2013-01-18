<?php


function action_creer_miniature_dist() {
	include_spip('inc/acces');
	if ($i = _request('id_auteur')
	AND $id_me = _request('id_me')
	AND $img = _request('img')
	AND $max = _request('max')
	AND $sec = _request('sec')
	AND verifier_low_sec($i, $sec, "miniature $max $img $id_me")) {
		include_spip('seenthis_fonctions');
		include_spip('inc/distant');
		spip_log('debut copie locale', 'cache');
		if (copie_locale($img)
		AND $image = afficher_miniature($img,$max)
		AND $miniature = extraire_balise($image,'img')) {
			spip_log('fin copie locale', 'cache');
			cache_me($id_me);
			include_spip('inc/headers');
			redirige_par_entete(extraire_attribut($miniature,'src'));
		}

		# si l'image n'existe pas (ou n'est pas accessible)
		else {
			include_spip('inc/headers');
			redirige_par_entete(find_in_path('imgs/verif_no.png'));
		}
	}
	else {
		@header('');
	}
}

