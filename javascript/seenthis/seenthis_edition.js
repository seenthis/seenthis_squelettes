/**
 * Les urls qu'on a déjà validées comme étant des images, pour éviter l'effet de clignotement
 */
var suivreEditionImagesValides = {};

/**
 * Les urls qu'on a déjà validées comme n'étant pas des images, pour éviter l'effet de clignotement
 */
var suivreEditionImagesInvalides = {};

/**
 * Id du lien actuel, pour avoir un id unique.
 */
var idLienActuel = 0;

/**
 * Id de l'auteur actuel, pour avoir un id unique.
 */
var idAuteurActuel = 0;

/**
 * Créé les images pour la fonction suivreEdition.
 * @param parentDiv le div parent où ajouter le html
 * @param imageUrl l'url de l'image à ajouter
 * @param lienId l'id du lien à supprimer
 * @param elementsLiens l'élement qui contient toutes les liens
 * @param masqueLiens fonction qui masque les liens
 * @param afficheImage fonction qui affiche les liens
 * @param afficheMessageLienDejaPoste function qui sgère les liens déjà postés
 */
function suivreEditionCreateImage(parentDiv, imageUrl, lienId, elementsLiens, afficheImage, masqueLiens, afficheMessageLienDejaPoste) {
	var tmpImg = $('<img>')
		.on('load', function () {
			suivreEditionImagesValides[imageUrl] = true;
			tmpImg.appendTo(parentDiv);
			$("#" + lienId).remove();
			afficheImage();
			if (elementsLiens.find('.lien').length == 0) {
				masqueLiens();
			}
		})
		.on('error', function () {
			// pas une image
			suivreEditionImagesInvalides[imageUrl] = true;
			afficheMessageLienDejaPoste(imageUrl, lienId);
		})
		.attr('src', imageUrl);
}

$.fn.suivreEdition = function () {

	function afficheMessageLienDejaPoste(url, lienId) {
		$.messageUrlsPourUrl(url, function (messageUrlsPourUrl) {
			var htmlLiens = "";
			for (var i = 0; i < messageUrlsPourUrl.length; i++) {
				var messageUrl = messageUrlsPourUrl[i];
				htmlLiens += '<a class="lienDejaPoste" href="' + messageUrl + '">⇗</a>';
			}
			$("#" + lienId).append(htmlLiens);
		});
	}

	var area = this;
	var personneAffiches = false;
	var tagsAffiches = false;
	var liensAffiches = false;
	var imagesAffiches = false;

	var currentForm = area.parent("div").parent("form");
	var personnesHtml = currentForm.find(".people");
	var imagesHtml = currentForm.find(".images");
	var tagsHtml = currentForm.find(".tags");
	var liensHtml = currentForm.find(".liens");

	var masqueLiens = function () {
		if (liensAffiches) {
			liensHtml.slideUp();
			liensAffiches = false;
		}
	};
	var afficheImage = function () {
		if (!imagesAffiches) {
			imagesHtml.slideDown();
			imagesAffiches = true;
		}
	};

	function masqueSiAffiche(variable, element) {
		if (variable) {
			element.slideUp();
		}
		return false;
	}

	function afficheSiMasque(variable, element) {
		if (!variable) {
			element.slideDown();
		}
		return true;
	}

	function verifieAuteur(nom, idElement) {
		$.existeAuteur(nom, function (result) {
			$('#' + idElement).addClass(result ? 'dieseValide' : 'dieseInvalide');
		})

	}

	var textUpdated = function () {
		var texteMessage = area.val() || '';

		// personnes
		var matchPersonne = texteMessage.match(reg_personne);
		if (matchPersonne) {
			var personnes = "<div class='titre_people'>Auteurs:</div>";
			var idAuteursATrouver = [];
			for (i = 0; i < matchPersonne.length; ++i) {
				var personne = matchPersonne[i];
				var nomPersonne = personne.substr(1, 1000);
				var lienPersonne = "people/" + nomPersonne;

				var hashClass = 'diese';
				var idAuteurATrouver = null;
				if ($.loginsAuteurs().hasOwnProperty(nomPersonne)) {
					hashClass = $.loginsAuteurs()[nomPersonne] ? 'dieseValide' : 'dieseInvalide';
				} else {
					idAuteurATrouver = 'auteur_a_trouver_' + idAuteurActuel;
					idAuteursATrouver.push({nom: nomPersonne, id: idAuteurATrouver});
					idAuteurActuel++;
				}
				personnes += "<span class='nom'>" +
				"<span " + (idAuteurATrouver ? ("id='" + idAuteurATrouver + "'") : '') + "class='" + hashClass + "'>@</span>" +
				"<a href=\"" + lienPersonne + "\" class='spip_out'>" + nomPersonne + "</a>" +
				"</span>";
			}
			personnesHtml.html(personnes);
			for (var k = 0; k < idAuteursATrouver.length; k++) {
				var idAuteurATrouver = idAuteursATrouver[k];
				verifieAuteur(idAuteurATrouver.nom, idAuteurATrouver.id);
			}
			personneAffiches = afficheSiMasque(personneAffiches, personnesHtml);
		} else {
			personneAffiches = masqueSiAffiche(personneAffiches, personnesHtml);
		}

		// tags
		var matchTag = texteMessage.match(reg_tag);
		if (matchTag) {
			var tags = "<div class='titre_tags'>Thèmes:</div>";
			for (var i = 0; i < matchTag.length; ++i) {
				var tag = matchTag[i].toLowerCase();
				var lienMessage = tag.substr(1, 1000);
				tags += "<span class='hashtag'><span class='diese'>#</span><a href=\"tag/" + lienMessage + "\" class='spip_out'>" + lienMessage + "</a></span>";
			}
			tagsHtml.html(tags);
			tagsAffiches = afficheSiMasque(tagsAffiches, tagsHtml);
		} else {
			tagsAffiches = masqueSiAffiche(tagsAffiches, tagsHtml);
		}

		// liens
		var matchUrl = texteMessage.match(reg_url);
		if (matchUrl) {
			var liens = "<div class='titre_links'>Liens:</div>";
			var images = "<div class='titre_images'>Images:</div>";
			var nombreDeLiens = 0;
			var nombreDImages = 0;

			var liensAVerifier = [];

			for (i = 0; i < matchUrl.length; ++i) {
				var lienUrl = matchUrl[i];

				if (suivreEditionImagesValides[lienUrl]) {
					nombreDImages++;
					images += "<img src=\"" + lienUrl + "\">";
				} else {
					nombreDeLiens++;
					var lienAff = lienUrl.replace(racine_url_match, "<span>$1</span>");
					lienAff = lienAff.replace(fin_url_match, "<span>$1</span>");
					// si c'est un lien pas encore testé il peut s'agir d'une image
					var lienId = 'possibleImage_' + idLienActuel;
					idLienActuel++;
					if (!suivreEditionImagesInvalides[lienUrl]) {
						liens += "<div id='" + lienId + "' class='lien'><a href=\"" + lienUrl + "\" class='spip_out'>" + lienAff + "</a></div>";
						suivreEditionCreateImage(
							imagesHtml,
							lienUrl,
							lienId,
							liensHtml,
							afficheImage,
							masqueLiens,
							afficheMessageLienDejaPoste);
					} else {
						liens += "<div id='" + lienId + "'class='lien'><a href=\"" + lienUrl + "\" class='spip_out'>" + lienAff + "</a></div>";
						liensAVerifier.push({url: lienUrl, id: lienId});
					}
				}
			}
			liensHtml.html(liens);
			for (var l = 0; l < liensAVerifier.length; l++) {
				var lienAVerifier = liensAVerifier[l];
				afficheMessageLienDejaPoste(lienAVerifier.url, lienAVerifier.id);
			}
			imagesHtml.html(images);

			if (nombreDeLiens > 0) {
				liensAffiches = afficheSiMasque(liensAffiches, liensHtml);
			} else {
				liensAffiches = masqueSiAffiche(liensAffiches, liensHtml);
			}
			if (nombreDImages > 0) {
				imagesAffiches = afficheSiMasque(imagesAffiches, imagesHtml);
			} else {
				imagesAffiches = masqueSiAffiche(imagesAffiches, imagesHtml);
			}
		} else {
			liensAffiches = masqueSiAffiche(liensAffiches, liensHtml);
			imagesAffiches = masqueSiAffiche(imagesAffiches, imagesHtml);
		}
	};

	$(area).typeWatch({
		callback: textUpdated,
		wait: 1000,
		captureLength: 0
	});
	if ((area.val() || '') != '') {
		textUpdated();
	}
	$(area).textcomplete([
		{ // html
			match: reg_personne_local,
			search: function (term, callback) {
				callback($.map($.listeLoginsAuteurs(), function (mention) {
					return mention.indexOf(term) === 0 ? mention : null;
				}));
			},
			index: 1,
			replace: function (mention) {
				return '@' + mention + ' ';
			}
		}
	]);

};