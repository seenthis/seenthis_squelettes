
/**
 * Les urls qu'on a déjà validées comme étant des images, pour éviter l'effet de clignotement
 * @type {{}}
 */
var suivreEditionImagesValides = {};
/**
 * Les urls qu'on a déjà validées comme n'étant pas des images, pour éviter l'effet de clignotement
 * @type {{}}
 */
var suivreEditionImagesInvalides = {};

/**
 * Créé les images pour la fonction suivreEdition.
 * @param parentDiv le div parent où ajouter le html
 * @param imageUrl l'url de l'image à ajouter
 * @param elementLien l'élément "lien", à supprimer quand l'image est chargée
 * @param elementImages l'élement qui contient toutes les images
 * @param elementsLiens l'élement qui contient toutes les liens
 * */
function suivreEditionCreateImage(parentDiv, imageUrl, elementLien, elementImages, elementsLiens){
    var tmpImg = $('<img>').on('load', function(){
        suivreEditionImagesValides[imageUrl] = true;
        tmpImg.appendTo(parentDiv);
        elementLien.remove();
        elementImages.slideDown();
        if(elementsLiens.find('.lien').length == 0) {
            elementsLiens.slideUp('fast');
        }
    }).on('error', function(){
        suivreEditionImagesInvalides[imageUrl] = true;
    }).attr('src', imageUrl);
}

$.fn.suivreEdition = function () {
    var area = this;
    var texteMessage = area.val() || '';
    var currentForm = area.parent("div").parent("form");

    var matchPersonne = texteMessage.match(reg_personne);
    var personnesHtml = currentForm.find(".people");
    var personnes = "<div class='titre_people'>Auteurs:</div>";
    if (matchPersonne) {
        for (i = 0; i < matchPersonne.length; ++i) {
            var personne = matchPersonne[i];
            var nomPersonne = personne.substr(1, 1000);
            var lienPersonne = "people/" + nomPersonne;
            personnes += "<span class='nom'><span class='diese'>@</span><a href=\"" + lienPersonne + "\" class='spip_out'>" + nomPersonne + "</a></span>";
        }
        personnesHtml.html(personnes);
        personnesHtml.slideDown();

    } else {
        personnesHtml.slideUp();
    }

    // tags
    var matchTag = texteMessage.match(reg_tag);
    var tagsHtml = currentForm.find(".tags");
    var tags = "<div class='titre_tags'>Thèmes:</div>";
    if (matchTag) {
        for (var i = 0; i < matchTag.length; ++i) {
            var tag = matchTag[i].toLowerCase();
            var lienMessage = tag.substr(1, 1000);
            tags += "<span class='hashtag'><span class='diese'>#</span><a href=\"tag/" + lienMessage + "\" class='spip_out'>" + lienMessage + "</a></span>";
        }
        tagsHtml.html(tags);
        tagsHtml.slideDown();
    } else {
        tagsHtml.slideUp();
    }

    // liens
    var matchUrl = texteMessage.match(reg_url);
    var liensHtml = currentForm.find(".liens");
    var liens = "<div class='titre_links'>Liens:</div>";

    var imagesHtml = currentForm.find(".images");
    imagesHtml.html("<div class='titre_images'>Images:</div>");
    if (matchUrl) {
        liensHtml.html(liens);
        var nombreDeLiens = 0;
        var nombreDImages = 0;

        for (i = 0; i < matchUrl.length; ++i) {
            var lienUrl = matchUrl[i];

            if(suivreEditionImagesValides[lienUrl]) {
                nombreDImages++;
                $("<img src=\"" + lienUrl + "\">").appendTo(imagesHtml)
            } else {
                nombreDeLiens++;
                var lienAff = lienUrl.replace(racine_url_match, "<span>$1</span>");
                lienAff = lienAff.replace(fin_url_match, "<span>$1</span>");
                var elementLien = $("<div class='lien'>⇧<a href=\"" + lienUrl + "\" class='spip_out'>" + lienAff + "</a></div>").appendTo(liensHtml);
                // si c'est un lien pas encore testé il peut s'agir d'une image
                if(! suivreEditionImagesInvalides[lienUrl]) {
                    suivreEditionCreateImage(imagesHtml, lienUrl, elementLien, imagesHtml, liensHtml);
                }
            }
        }
        if(nombreDeLiens > 0) {
            liensHtml.slideDown();
        } else {
            liensHtml.slideUp();
        }
        if(nombreDImages > 0) {
            imagesHtml.slideDown();
        } else {
            imagesHtml.slideUp();
        }
    } else {
        liensHtml.slideUp();
        imagesHtml.slideUp();
    }

};
