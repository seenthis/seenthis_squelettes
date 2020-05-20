$(function () {
	function preparerMessage(contenu, longeurCible) {
		if (contenu.length > longeurCible) {
			var texteCoupe = contenu.substring(0, longeurCible - 1);
			var dernierEspace = texteCoupe.lastIndexOf(' ');
			return texteCoupe.substring(0, dernierEspace) + '…'
		} else {
			return contenu;
		}
	}

	function urlCourte(article) {
		var idArticle = parseInt(article.attr('data-article-id')).toString(36);
		return "http://" + shortHost + "/" + idArticle;
	}

	function texteArticle(article) {
		$('meta[name="twitter:description"]').attr('content');
	}

	if (liensPartageFacebook || liensPartageTwitter) {
		function ajouterLiensPartage() {
			$('article:not(.aLiensPartage)').each(function (_, articleDom) {
				var article = $(articleDom);
				article.addClass('aLiensPartage');
				var elementInsertion = article.find('.texte_reponse');

				if (liensPartageTwitter && (article.find('.twitter').length == 0)) {
					var lienTwitter = $(
						'<div class="twitter">' +
						'<a href="#" class="spip_out" title="Twitter">' +
						'<img src="plugins/seenthis_squelettes/imgs/twitter.png" srcset="plugins/seenthis_squelettes/imgs/twitter.x2.png 2x" alt="">' +
						'</a></div>').prependTo(elementInsertion);
					lienTwitter.click(function () {
						var urlPrepare = urlCourte(article);
						var textePrepare = preparerMessage(texteArticle(article), 280 - (urlPrepare.length + 1));
						textePrepare += " " + urlPrepare;
						lienTwitter.find('a').attr('href', "http://twitter.com/intent/tweet?text=" + encodeURIComponent(textePrepare));
						return true;
					});
				}
				if (liensPartageFacebook && (article.find('.facebook').length == 0)) {
					var lienFacebook = $(
						'<div class="facebook">' +
						'<a href="#" class="spip_out" title="Facebook">' +
						'<img src="plugins/seenthis_squelettes/imgs/facebook.gif" srcset="plugins/seenthis_squelettes/imgs/facebook.x2.png 2x" alt="">' +
						'</a></div>').prependTo(elementInsertion);
					lienFacebook.click(function () {
						var textePrepare = preparerMessage(texteArticle(article), 250);
						var urlPrepare = urlCourte(article);
						lienFacebook.find('a').attr('href', "http://www.facebook.com/sharer.php?u=" + encodeURIComponent(urlPrepare) + "&amp;t=" + encodeURIComponent(textePrepare));
						return true;
					});
				}

			});
		}

		ajouterLiensPartage();
		$(document).ajaxComplete(function () {
			ajouterLiensPartage();
		});

	}
});
