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
		var texteArticle = article.find('.texte').text();
		return texteArticle.replace(/(\r\n|\n|\r|\t)/gm, " ");
	}

	if (liensPartages) {

		$('article').each(function (_, articleDom) {

			var article = $(articleDom);
			var elementInsertion = article.find('.texte_reponse');

			if (article.find('.twitter').length == 0) {
				var lienTwitter = $(
					'<div class="twitter">' +
					'<a href="#" class="spip_out" title="Twitter">' +
					'<img src="plugins/seenthis_squelettes/imgs/twitter.png" srcset="plugins/seenthis_squelettes/imgs/twitter.x2.png 2x" alt="">' +
					'</a></div>').prependTo(elementInsertion);
				lienTwitter.click(function () {
					var urlPrepare = urlCourte(article);
					var textePrepare = preparerMessage(texteArticle(article), 140 - (urlPrepare.length + 1));
					textePrepare += " " + urlPrepare;
					lienTwitter.find('a').attr('href', "http://twitter.com/intent/tweet?text=" + encodeURIComponent(textePrepare));
					return true;
				});
			}
			if (article.find('.facebook').length == 0) {
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
});
