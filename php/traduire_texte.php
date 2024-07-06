<?php

function translate_requestCurl($parameters) {
	$url_page = 'https://ajax.googleapis.com/ajax/services/language/translate?';
	$url_page = 'https://www.googleapis.com/language/translate/v2?';


	$parameters_explode = explode('&', $parameters);
	$nombre_param = count($parameters_explode);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url_page);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
	curl_setopt($ch, CURLOPT_POST, $nombre_param);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-HTTP-Method-Override: GET']);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$body = curl_exec($ch);
	curl_close($ch);

	$json = json_decode($body, true);

	if (isset($json['error'])) {
		spip_log($json, 'translate');
		return false;
	}
	return urldecode($json['data']['translations'][0]['translatedText']);
}

function translate_requestCurl_bing($apikey, $text, $srcLang, $destLang) {
	// Bon sang, si tu n'utilises pas .NET, ce truc est documenté par les corbeaux
	// attaquer le machin en SOAP (la méthode HTTP ne convient que pour des textes très courts (GET, pas POST)

	$client = new SoapClient('http://api.microsofttranslator.com/V2/Soap.svc');

	$params = [
		'appId' => $apikey,
		'text' => $text,
		'from' => $srcLang,
		'to' => $destLang];
	try {
		$translation = $client->translate($params);
	} catch (Exception $e) {
		return false;
	}

	return $translation->TranslateResult;
}


function traduire_texte($text, $destLang = 'fr', $srcLang = 'en') {

	//$text = rawurlencode( $text );
	$destLang = urlencode($destLang);
	$srcLang = urlencode($srcLang);
	$trans = '';

	if (defined('_BING_APIKEY')) {
		//echo "BING";
		$trans = translate_requestCurl_bing(_BING_APIKEY, $text, $srcLang, $destLang);
	}

	elseif (defined('_GOOGLETRANSLATE_APIKEY')) {
		$trans = translate_requestCurl('key=' . _GOOGLETRANSLATE_APIKEY . "&source=$srcLang&target=$destLang&q=" . rawurlencode($text));
	}

	elseif (defined('_TRANSLATESHELL_CMD')) {
		$trans = translate_shell($text, $destLang);
	}

	$ltr = lang_dir($destLang, 'ltr', 'rtl');

	if (strlen($trans)) {
		return "<div dir='$ltr' lang='$destLang'>$trans</div>";
	} else {
		return false;
	}
}

function translate_shell($text, $destLang = 'fr') {
	$prep = str_replace("\n", ' ', html2unicode($text));
	$prep = preg_split(",<p\b[^>]*>,i", $prep);
	$trans = [];
	foreach ($prep as $k => $line) {
		if ($k > 0) {
			$trans[] = '<p>';
		}
		$line = preg_replace(',<[^>]*>,i', ' ', $line);
		// max line = 1000 chars
		$a = [];
		while (mb_strlen($line) > 1000) {
			$debut = mb_substr($line, 0, 600);
			$suite = mb_substr($line, 600);
			$point = strpos($suite, '.');

			// chercher une fin de phrase pas trop loin
			// ou a defaut, une virgule ; au pire un espace
			if ($point === false) {
				$point = strpos(preg_replace('/[,;?:!]/', ' ', $suite), ' ');
			}
			if ($point === false) {
				$point = strpos($suite, ' ');
			}
			if ($point === false) {
				$point = 0;
			}
			$a[] = trim($debut . mb_substr($suite, 0, 1 + $point));
			$line = mb_substr($line, 600 + 1 + $point);
		}
		$a[] = trim($line);
		foreach ($a as $l) {
			spip_log('IN: ' . $l, 'translate');
			$trad = translate_line($l, $destLang);
			spip_log('OUT: ' . $trad, 'translate');
			$trans[] = $trad;
		}
	}

	return join("\n", $trans);
}

function translate_line($l, $destLang) {
	if (strlen(trim($l)) == 0) {
		return '';
	}
	$descriptorspec = [
		0 => ['pipe', 'r'],
		1 => ['pipe', 'w']
	];
	$cmd = _TRANSLATESHELL_CMD . ' -b ' . ':' . escapeshellarg($destLang);
	$cmdr = proc_open($cmd, $descriptorspec, $pipes);
	$trad = '';
	if (is_resource($cmdr)) {
		fwrite($pipes[0], $l) && fclose($pipes[0]);
		$trad = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
	}
	return $trad;
}


function traduire($text, $destLang = 'fr', $srcLang = 'en') {
	if (defined('_BING_APIKEY')) {
		$text = mb_substr($text, 0, 10000, 'UTF-8');
	} elseif (defined('_GOOGLETRANSLATE_APIKEY')) {
		$text = mb_substr($text, 0, 4500, 'UTF-8');
	}



	$hash = md5($text);

	$query = sql_select('texte', 'spip_traductions', "hash='$hash' AND langue ='$destLang'");

	if ($row = sql_fetch($query)) {
		$trad = $row['texte'];
		# echo "EN BASE : ".$hash;
		return $trad;
	} else {
		 //echo "NOUVEAU";
		$trad = traduire_texte($text, $destLang, $srcLang);
		if ($trad) {
			spip_log('[' . $destLang . "] $text \n === $trad", 'translate');
			sql_insertq(
				'spip_traductions',
				[
					'hash' => $hash,
					'texte' => $trad,
					'langue' => $destLang
				]
			);
			return $trad;
		}
	}
}
