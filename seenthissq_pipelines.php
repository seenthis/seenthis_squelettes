<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Insertion dans le pipeline post_insertion
 * 
 * Lors de l'inscription depuis la page d'un auteur, ajouter celui-ci à la liste des suivis du compte créé
 * 
 * @param array $flux
 * 		Le contexte du pipeline
 * @return array $flux
 */
function seenthissq_post_insertion($flux){
	if ($flux['args']['table'] == 'spip_auteurs') {
		if ($auteur_page = _request('auteur_page')) {
			sql_insertq("spip_me_follow",
				array(
					"id_follow" => $flux['args']['id_objet'],
					"id_auteur" => $auteur_page,
					"date" => "NOW()"
				)
			);
		}
	}
	return $flux;
}