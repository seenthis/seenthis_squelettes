$(function () {
	var messageUrlsPourUrls = {};

	/**
	 * Récupère les urls des messages qui contiennent cette url
	 * @param url l'url.
	 * @param block appellé en callback avec le résultat sous forme de tableau.
	 */
	$.messageUrlsPourUrl = function (url, block) {
		if (messageUrlsPourUrls[url]) {
			block(messageUrlsPourUrls[url]);
		} else {
			$.getJSON("spip.php?action=messages_lien&url=" + encodeURIComponent(url), function (data) {
				if (messageUrlsPourUrls[url]) {
					block(messageUrlsPourUrls[url]);
				} else {
					var messageUrls = data.urlMessages;
					messageUrlsPourUrls[url] = messageUrls;
					block(messageUrls);
				}
			});
		}
	};
});
