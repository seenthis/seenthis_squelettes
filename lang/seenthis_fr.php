<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
// Fichier source, a modifier dans https://github.com/seenthis/seenthis_squelettes/trunk/lang/
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$GLOBALS[$GLOBALS['idx_lang']] = array(

	// Z
	'Z' => 'ZZzZZzzz',

	// A
	'accueil' => 'Accueil',
	'annuler' => 'Annuler',
	'auteur_block' => 'Bloquer @people@',
	'auteur_block_you' => 'Cet auteur vous a bloqué. Vous ne pouvez pas commenter ses messages.',
	'auteur_ne_plus_block' => 'Débloquer @people@',
	'auteur_ne_plus_suivre' => 'Ne plus suivre @people@',
	'auteur_vous_block' => 'Vous avez bloqué @people@',
	'auteur_vous_suivez' => 'Vous suivez @people@',
	'auteurs_vous_suivez' => 'Les auteurs que vous suivez',

	// B
	'beta_publique' => '<strong>Seenthis est en beta publique.</strong><br />Vous pouvez vous inscrire et participer, merci pour votre patience et votre bonne volonté durant cette phase.',
	'bookmarklet_descriptif' => 'Pour l’utiliser : lorsque vous visitez la page d’un article que vous souhaitez référencer, sélectionnez l’extrait du texte à citer, et cliquez sur votre bookmarklet. Votre interface Seenthis s’ouvre alors, et votre message est pré-rempli avec le titre de la page, l’URL et la citation.',
	'bookmarklet_titre' => 'Faites glisser ce bookmarklet dans votre barre de favoris :',

	// C
	'changement_email_existe' => 'Cette adresse email est déjà utilisée par un autre compte',
	'changement_email_informer' => '@login@ a indiqué une nouvelle adresse d’email : "@new@" (en remplacement de "@old@")',
	'changement_email_subject' => 'Nouvelle adresse email pour @login@',

	// E
	'ecrire_commentaire' => 'Écrire un commentaire',
	'effacer' => 'effacer',
	'envoyer' => 'Envoyer (maj+retour)',

	// F
	'favori' => 'partager',
	'flux_desactive_titre' => 'Import de votre flux désactivé sur @nom_site@',
	'flux_desactive_texte' => 'Bonjour @nom@,
L’import automatique de votre flux RSS/ATOM a été désactivé car cela fait un moment que vous ne vous êtes pas connecté au site.
Vous pouvez le réactiver depuis votre page de préférences, en supprimant l’étoile ajoutée au début de son adresse.',

	// I
	'insecable_typo' => ' ',
	'intitule_connexion' => 'connexion',

	// L
	'logout' => 'se déconnecter',

	// M
	'me_suggerer_contacts' => 'Me suggérer des contacts',
	'message_inscription' => '<strong>Inscription gratuite et immédiate</strong><br />Dès l’enregistrement, votre mot de passe vous est expédié par email.',
	'message_suggerer' => '
				<strong>Nouveau sur Seenthis ? Vous devriez commencer par suivre des auteurs.</strong>
				<p>Cliquez sur le bouton « Me suggérer des contacts » ci-contre, et 
				visitez les pages des auteurs proposés. Si un auteur vous intéresse, suivez-le en cliquant, 
				en haut de page, sur « Suivre cet auteur ».
				Puis revenez sur cette page, recommencez l’opération, les propositions s’affineront au fur et
				à mesure de votre propre sélection d’auteurs.</p>
				<p>Ce message disparaîtra lorsque vous suivrez cinq auteurs. 
				Rendez-vous ensuite  sur l’onglet « @auteurs » pour obtenir des suggestions d’auteurs.</p>',
	'modifier' => 'modifier',
	'montrer_messages' => 'montrer les @total@ messages',

	// P
	'pave_accueil' => 'Du <strong>short-blogging</strong> sans limite de caractères. De la <strong>recommandation de liens</strong>. Des <strong>automatismes</strong> pour rédiger facilement vos messages. Des <strong>forums</strong> sous chaque billet. De la <strong>veille d’actualité</strong>. Une <strong>thématisation</strong> avancée.',
	'people' => 'auteurs',
	'profil' => 'préférences',
	'profil_alerte_conversations' => '<b>conversations</b> / quelqu’un répond à un billet auquel j’ai moi-même répondu',
	'profil_alerte_dubien' => '<b>un ami qui vous veut du bien</b> / quelqu’un me suit',
	'profil_alerte_mes_billets' => '<b>mes billets</b> / recevoir une copie de mes propres messages',
	'profil_alerte_nolife' => '<b>nolife</b> / un auteur que je suis répond à n’importe quel billet',
	'profil_alerte_nouveaux_billets' => '<b>nouveaux billets</b> / un nouveau billet est posté par un auteur que je suis',
	'profil_alerte_partage' => '<b>partage</b> / quelqu’un a partagé un de mes billets',
	'profil_alerte_reponse_partage' => '<b>réponses à un partage</b> / quelqu’un répond à un billet que j’ai partagé',
	'profil_alerte_reponses' => '<b>réponses à mes billets</b> / quelqu’un répond à un de mes billets',
	'profil_alertes' => 'Alertes',
	'profil_copyleft' => 'Copyright, copyleft',
	'profil_copyright_classique' => 'Pas de licence spécifique (droits d’auteur par défaut)',
	'profil_couleur' => 'Votre couleur',
	'profil_entete' => 'Bandeau supérieur (960 pixels de large)',
	'profil_fond' => 'Image de fond (1600px x 1200px au maximum)',
	'profil_graphisme' => 'Votre graphisme',
	'profil_identite' => 'Identité',
	'profil_langue' => 'Ma langue',
	'profil_licence' => 'Licence de vos messages',
	'profil_liens_partage_fb' => 'Afficher les boutons de partage vers Facebook',
	'profil_liens_partage_tw' => 'Afficher les boutons de partage vers Twitter',
	'profil_logo' => 'Logo de l’auteur (carré)',
	'profil_mexpedier' => 'M’expédier un courrier<br /> électronique quand...',
	'profil_partage' => 'Partage',
	'profil_rss' => 'Importer automatiquement un flux d’articles au format ATOM ou RSS',

	// R
	'raccourci_bold' => '*<b>gras</b>*',
	'raccourci_italic' => '_<i>italique</i>_',
	'raccourci_quote' => '❝citation❞ (maj+tab)',
	'raccourci_strike' => '-<del>barré</del>-',

	// S
	'slogan_lien' => 'Inscrivez-vous, c’est gratuit et rapide !',
	'slogan_texte' => '<strong>Participez à la discussion !</strong> Sur Seenthis, vous pouvez référencer des articles, les commenter, écrire des billets, discuter avec vos amis, suivre les auteurs qui vous intéressent...',
	'suggestions' => 'Suggestions :',
	'suivre_people' => 'Suivre @people@',
	'suivre_url' => 'Suivre ce site',
	'suivre_url_stop' => 'Ne plus suivre ce site',
	'suivre_url_you' => 'Vous suivez ce site',
	'supprimer_mon_profil' => 'Supprimer mon profil',
	'supprimer_mon_profil_au_revoir' => 'Suppression en cours… au revoir !',
	'supprimer_mon_profil_irreversible' => 'Supprimer mon profil (irréversible)',
	'supprimer_mon_profil_login' => 'Veuillez saisir votre login pour confirmer la demande',
	'supprimer_veuillez_saisir' => 'Vérifiez votre login',

	// T
	'tags' => 'thèmes',
	'theme_automatiquement' => 'Ce <b>thème</b> a été généré <b>automatiquement</b>.',
	'theme_manuellement' => 'Ce <b>thème</b> est attribué <b>manuellement</b> par les auteurs des messages.',
	'themes_automatiques' => 'thèmes automatiques',
	'themes_vous_suivez' => 'Les thèmes que vous suivez',
	'tous_les_messages_de' => 'Tous les messages de @people@',
	'traduire_avec_google' => 'traduire',

	// U
	'urls' => 'sites',
	'urls_vous_suivez' => 'Les sites que vous suivez',

	// V
	'votre_message' => 'Votre message',
	'votre_message_public' => 'Votre message public à ',
	'vous_connaissez' => 'Vous connaissez peut-être :',
	'vous_suivent' => 'abonnés',
	'vous_suivez' => 'abonnements'
);
