<?php

function action_api_url() {
	$code = _request('code');

	$site = [];
	$messages = [];
	$url = '';

	// md5 ?
	if (preg_match(',^[0-9a-f]{32}$,i', $code)) {
		$code = strtolower($code);
		$site = recherche_site_par_md5($code);
	}
	/*
	else if (preg_match(',^https?:,', $code)) {
		$site = recherche_site_par_url($code);
	}
	else
		$site = "$code";
	*/

	foreach ($site as $s) {
		$url = $s['url_site'];
		foreach (sql_allfetsel('*', 'spip_me_syndic', 'id_syndic=' . $s['id_syndic']) as $m) {
			$messages[] = intval($m['id_me']);
		}
	}

	$rep = [
		'status' => (($site and $messages) ? 'success' : 'fail'),
		'url' => $url,
		'messages' => $messages,
	];

	@header('Content-Type: text/plain; charset=utf-8');
	echo json_encode($rep);
}


function recherche_site_par_md5($code) {
	// attention seenthis bouffe le / final d'une URL,
	// on a donc deux md5 possibles
	$f = sql_allfetsel('id_syndic, url_site', 'spip_syndic', '(MD5(url_site)=' . _q($code) . ' OR MD5(CONCAT(url_site,"/"))=' . _q($code) . ')');
	return $f;
}
