<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Insertion dans le pipeline post_insertion
 *
 * Lors de l'inscription depuis la page d'un auteur, ajouter celui-ci à la liste des suivis du compte créé
 *
 * @param array $flux
 * 		Le contexte du pipeline
 * @return array $flux
 */
function seenthissq_post_insertion($flux) {
	if ($flux['args']['table'] == 'spip_auteurs') {
		if ($auteur_page = _request('auteur_page')) {
			sql_insertq(
				'spip_me_follow',
				[
					'id_follow' => $flux['args']['id_objet'],
					'id_auteur' => $auteur_page,
					'date' => 'NOW()'
				]
			);
		}
	}
	return $flux;
}

/**
 * Insertion dans le pipeline trig_auth_trace
 *
 * Lors du logout d'un auteur, repasser la valeur du champ en_ligne à la date courante
 *
 * @param array $flux
 * 		Le contexte du pipeline
 * @return array $flux
 */
function seenthissq_trig_auth_trace($flux) {
	if ($flux['args']['date'] == '0000-00-00 00:00:00') {
		include_spip('inc/session');
		// si c'est l'auteur actuellement connecté qui se déconnecte
		if (session_get('id_auteur') and $flux['args']['row']['id_auteur'] == session_get('id_auteur')) {
			sql_updateq('spip_auteurs', ['en_ligne' => date('Y-m-d H:i:s')], 'id_auteur=' . $flux['args']['row']['id_auteur']);
		}
	}
	return $flux;
}

/**
 * Insertion dans le pipeline preparer_visiteur_session
 *
 * Lors du login d'un auteur, passer la valeur du champ en_ligne à la date courante (même dans l'espace public)
 *
 * @param array $flux
 * 		Le contexte du pipeline
 * @return array $flux
 */
function seenthissq_preparer_visiteur_session($flux) {
	if (!empty($flux['args']['row']['id_auteur'])) {
		sql_updateq('spip_auteurs', ['en_ligne' => date('Y-m-d H:i:s')], 'id_auteur=' . $flux['args']['row']['id_auteur']);
	}
	return $flux;
}

/**
 * Optimiser la base de donnée en supprimant les liens orphelins
 *
 * @param array $flux
 * @return array
 */
function seenthissq_optimiser_base_disparus($flux) {
	$n = &$flux['data'];
	# les followers dont le compte auteur a été supprimé
	$res = sql_select(
		'F.id_follow AS id',
		'spip_me_follow AS F
		        LEFT JOIN spip_auteurs AS auteur
		          ON F.id_follow=auteur.id_auteur',
		'auteur.id_auteur IS NULL'
	);

	$n += optimiser_sansref('spip_me_follow', 'id_follow', $res);
	return $flux;
}
