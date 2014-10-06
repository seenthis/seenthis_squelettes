<?php


function action_creer_miniature_dist() {
	include_spip('inc/acces');
	if ($id_me = _request('id_me')
	AND $img = _request('img')
	AND $max = _request('max')
	AND $sec = _request('sec')
	AND verifier_low_sec(1, $sec, "miniature $max $img $id_me")) {
		include_spip('seenthis_fonctions');
		include_spip('inc/distant');
		spip_log('debut copie locale '.$img, 'distant');
		if (copie_locale_safe($img)
		AND $image = afficher_miniature($img,$max)
		AND $miniature = extraire_balise($image,'img')) {
			spip_log('fin copie locale '.$img, 'distant');
			cache_message($id_me);
			spip_log("chercher parent $id_me", 'distant');
			if ($e = sql_allfetsel('id_me', 'spip_me', 'id_parent='.sql_quote($id_me))) {
				foreach($e as $enfant) {
					spip_log("enfant = $enfant[id_me]", 'distant');
					cache_message($enfant['id_me']);
				}
			}
			include_spip('inc/headers');
			$mini = extraire_attribut($miniature,'src');
			spip_log("miniature $img = $mini (me=$id_me, p=$p[id_parent])", 'distant');
			redirige_par_entete($mini);
		}

		# si l'image n'existe pas (ou n'est pas accessible)
		else {
			spip_log('echec copie locale '.$img, 'distant');
			include_spip('inc/headers');
			redirige_par_entete(find_in_path('imgs/verif_no.png'));
		}
	}
	# si le code de securite n'est pas bon
	else {
		spip_log('acces interdit', 'distant');
		include_spip('inc/headers');
		redirige_par_entete(find_in_path('imgs/verif_no.png'));
	}
}

