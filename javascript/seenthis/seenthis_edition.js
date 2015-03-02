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
 * Créé les images pour la fonction suivreEdition.
 * @param parentDiv le div parent où ajouter le html
 * @param imageUrl l'url de l'image à ajouter
 * @param lienId l'id du lien à supprimer
 * @param elementsLiens l'élement qui contient toutes les liens
 * @param masqueLiens fonction qui masque les liens
 * @param afficheImage fonction qui affiche les liens
 */
function suivreEditionCreateImage(parentDiv, imageUrl, lienId, elementsLiens, afficheImage, masqueLiens) {
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
            suivreEditionImagesInvalides[imageUrl] = true;
        })
        .attr('src', imageUrl);
}

$.fn.suivreEdition = function () {
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
        console.log('>masqueLiens');
        if (liensAffiches) {
            liensHtml.slideUp();
            liensAffiches = false;
        }
        console.log('<masqueLiens');
    };
    var afficheImage = function () {
        console.log('>afficheImage');
        if (!imagesAffiches) {
            imagesHtml.slideDown();
            imagesAffiches = true;
        }
        console.log('<afficheImage');
    };

    var textUpdated = function () {
        console.log('update');
        var texteMessage = area.val() || '';

        var matchPersonne = texteMessage.match(reg_personne);
        var personnes = "<div class='titre_people'>Auteurs:</div>";
        if (matchPersonne) {
            for (i = 0; i < matchPersonne.length; ++i) {
                var personne = matchPersonne[i];
                var nomPersonne = personne.substr(1, 1000);
                var lienPersonne = "people/" + nomPersonne;
                personnes += "<span class='nom'><span class='diese'>@</span><a href=\"" + lienPersonne + "\" class='spip_out'>" + nomPersonne + "</a></span>";
            }
            personnesHtml.html(personnes);
            if (!personneAffiches) {
                personnesHtml.slideDown();
                personneAffiches = true;
            }
        } else {
            if (personneAffiches) {
                personnesHtml.slideUp();
                personneAffiches = false
            }
        }

        // tags
        var matchTag = texteMessage.match(reg_tag);
        var tags = "<div class='titre_tags'>Thèmes:</div>";
        if (matchTag) {
            for (var i = 0; i < matchTag.length; ++i) {
                var tag = matchTag[i].toLowerCase();
                var lienMessage = tag.substr(1, 1000);
                tags += "<span class='hashtag'><span class='diese'>#</span><a href=\"tag/" + lienMessage + "\" class='spip_out'>" + lienMessage + "</a></span>";
            }
            tagsHtml.html(tags);
            if (!tagsAffiches) {
                tagsHtml.slideDown();
                tagsAffiches = true;
            }
        } else {
            if (tagsAffiches) {
                tagsHtml.slideUp();
                tagsAffiches = false;
            }
        }

        // liens
        var matchUrl = texteMessage.match(reg_url);
        var liens = "<div class='titre_links'>Liens:</div>";
        var images = "<div class='titre_images'>Images:</div>";

        if (matchUrl) {
            var nombreDeLiens = 0;
            var nombreDImages = 0;

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
                    if (!suivreEditionImagesInvalides[lienUrl]) {
                        var lienId = 'possibleImage_' + idLienActuel;
                        idLienActuel++;
                        liens += "<div id='" + lienId + "' class='lien'>⇧<a href=\"" + lienUrl + "\" class='spip_out'>" + lienAff + "</a></div>";
                        suivreEditionCreateImage(
                            imagesHtml,
                            lienUrl,
                            lienId,
                            liensHtml,
                            afficheImage,
                            masqueLiens);
                    } else {
                        liens += "<div class='lien'>⇧<a href=\"" + lienUrl + "\" class='spip_out'>" + lienAff + "</a></div>";
                    }
                }
            }
            liensHtml.html(liens);
            imagesHtml.html(images);

            if (nombreDeLiens > 0) {
                if (!liensAffiches) {
                    liensHtml.slideDown();
                    liensAffiches = true;
                }
            } else {
                if (liensAffiches) {
                    liensHtml.slideUp();
                    liensAffiches = false;
                }
            }
            if (nombreDImages > 0) {
                if (!imagesAffiches) {
                    imagesHtml.slideDown();
                    imagesAffiches = true;
                }
            } else {
                if (imagesAffiches) {
                    imagesHtml.slideUp();
                    imagesAffiches = false;
                }
            }
        } else {
            if (liensAffiches) {
                liensHtml.slideUp();
                liensAffiches = false;
            }
            if (imagesAffiches) {
                imagesHtml.slideUp();
                imagesAffiches = false;
            }
        }
    };

    $(area).typeWatch({
        callback: function () {
            textUpdated();
        },
        wait: 1000,
        captureLength: 0
    });

};