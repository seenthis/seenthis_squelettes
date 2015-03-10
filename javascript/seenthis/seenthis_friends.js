$(function () {
	var friends = {};
	var friendsList = [];

	/**
	 * Hash des logins des auteurs, contient un booleen suivant si on sait que l'auteur existe ou pas.
	 */
	$.loginsAuteurs = function () {
		return friends;
	};

	/**
	 * Tableau des logins des auteurs qui existent.
	 */
	$.listeLoginsAuteurs = function () {
		return friendsList;
	};

	/**
	 * Vérifie si un login auteur existe.
	 * @param login le nom.
	 * @param block appellé en callback avec le résultat sous forme de booleen.
	 */
	$.existeAuteur = function (login, block) {
		if (friends.hasOwnProperty(login)) {
			block(friends[login]);
		} else {
			$.getJSON("spip.php?action=auteur_existe&login_auteur=" + encodeURIComponent(login), function (data) {
				if (friends.hasOwnProperty(login)) {
					block(friends[login]);
				} else {
					var result = data.result;
					friends[login] = result;
					if (result) {
						friendsList.push(login);
					}
					block(result);
				}
			});
		}
	};

	if (auteur_connecte > 0) {
		$.getJSON("spip.php?action=liste_amis", function (data) {
			var logins = data.logins;
			for (var i = 0; i < logins.length; i++) {
				var login = logins[i];
				if (!friends[login]) {
					friends[login] = true;
					friendsList.push(login);
				}
			}
		});
	}
	$('[data-login-auteur]').each(function (_, element) {
		var loginAuteur = $(element).attr('data-login-auteur');
		if (!friends[loginAuteur]) {
			friendsList.push(loginAuteur);
			friends[loginAuteur] = true;
		}
	});
});
