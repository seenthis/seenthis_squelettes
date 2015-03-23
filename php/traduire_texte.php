<?php

function translate_requestCurl($parameters)
{
	$url_page = "https://ajax.googleapis.com/ajax/services/language/translate?";
	$url_page = "https://www.googleapis.com/language/translate/v2?";
	
	
	$parameters_explode = explode("&", $parameters);
	$nombre_param = count($parameters_explode);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url_page);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, !empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");
	curl_setopt($ch,CURLOPT_POST,nombre_param);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$parameters);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET')); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$body = curl_exec($ch);
	curl_close($ch);
	
	$json = json_decode( $body, true );

	if (isset($json["error"])) {
		spip_log($json, 'translate');
		return false;
	}
	return urldecode($json["data"]["translations"][0]["translatedText"]);
}

function translate_requestCurl_bing($apikey, $text, $srcLang, $destLang) {
	// Bon sang, si tu n'utilises pas .NET, ce truc est documenté par les corbeaux
	// attaquer le machin en SOAP (la méthode HTTP ne convient que pour des textes très courts (GET, pas POST)

	$client = new SoapClient("http://api.microsofttranslator.com/V2/Soap.svc");

	$params = array(
		'appId' => $apikey, 
		'text' => $text, 
		'from' => $srcLang, 
		'to' => $destLang);
	try {
		$translation = $client->translate($params);
	} catch(Exception $e) {
		return false;
	}
	
	return $translation->TranslateResult;
	

}	


function traduire_texte( $text, $destLang = 'fr', $srcLang = 'en' ) {

	//$text = rawurlencode( $text );
	$destLang = urlencode( $destLang );
	$srcLang = urlencode( $srcLang );

	if (defined('_BING_APIKEY')) {
		//echo "BING";
		$trans = translate_requestCurl_bing(_BING_APIKEY, $text, $srcLang, $destLang);
	}
	
	else if (defined('_GOOGLETRANSLATE_APIKEY')) {
		$trans = translate_requestCurl("key="._GOOGLETRANSLATE_APIKEY."&source=$srcLang&target=$destLang&q=".rawurlencode($text));
	}
	
	else if (defined('_TRANSLATESHELL_CMD')) {
		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w")
		);
		$cmd = proc_open(_TRANSLATESHELL_CMD.' -b '.escapeshellarg(':'.$destLang), $descriptorspec, $pipes);
		if (is_resource($cmd)) {
			$trans = preg_replace(',<.*?>,msS', ' ', $text); // remove <p>
			fwrite($pipes[0], html2unicode($trans)) && fclose($pipes[0]);
			$trans = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}
	}

	$ltr = lang_dir($destLang, 'ltr','rtl');
	
	if (strlen($trans))
		return "<div dir='$ltr' lang='$destLang'>$trans</div>";
	else
		return false;
}

function traduire($text, $destLang = 'fr', $srcLang = 'en') {
	if (defined("_BING_APIKEY")) {
		$text = mb_substr($text, 0, 10000, "UTF-8");
	} else {
		$text = mb_substr($text, 0, 4500, "UTF-8");
	}
	
	
	
	$hash = md5($text);
	
	$query = sql_select("texte", "spip_traductions", "hash='$hash' AND langue ='$destLang'");
	
	if ($row = sql_fetch($query)) {
		$trad = $row["texte"];
		# echo "EN BASE : ".$hash;
		return $trad;
	} else {
		 //echo "NOUVEAU";
		$trad = traduire_texte( $text, $destLang, $srcLang );
		if ($trad) {
			spip_log('['.$destLang."] $text \n === $trad", 'translate');
			sql_insertq("spip_traductions",
				array(
					"hash" => $hash,
					"texte" => $trad,
					"langue" => $destLang
				)
			);
			return $trad;
		}
	
	}
	

}
?>