<?php

function action_creer_miniature_dist() {
	include_spip('inc/acces');
	include_spip('inc/filtres');
	if ($img = _request('img')
	AND $maxw = _request('maxw')
	AND $maxh = _request('maxh')
	AND $sec = _request('sec')
	AND verifier_low_sec(1, $sec, "miniature $maxw $maxh $img")) {
		include_spip('seenthis_fonctions');
		include_spip('inc/distant');
		spip_log('debut copie locale '.$img, 'distant');
		if (copie_locale_safe($img)
		AND $image = calculer_miniature($img, $maxw, $maxh)
		AND $miniature = extraire_balise($image,'img')) {
			spip_log('fin copie locale '.$img, 'distant');
			echo $image;
			exit;
		}

		# si l'image n'existe pas (ou n'est pas accessible)
		else
			spip_log('echec copie locale '.$img, 'distant');
	}
	# si le code de securite n'est pas bon
	else
		spip_log('acces interdit', 'distant');

	echo inserer_attribut('<img />', 'src', find_in_path('imgs/verif_no.png'));
	exit;
}

