<?php

function translate_requestCurl($parameters)
{
	$url_page = "https://ajax.googleapis.com/ajax/services/language/translate?";

	
	
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
	
		if (isset($json["error"])) return false;
	//	return urldecode($json["data"]["translations"][0]["translatedText"]);
		return urldecode($json["responseData"]["translatedText"]);

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
	
	$translation = $client->translate($params);
	
	return $translation->TranslateResult;
	

}	


function traduire_texte( $text, $destLang = 'fr', $srcLang = 'en' ) {

	//$text = rawurlencode( $text );
	$destLang = urlencode( $destLang );
	$srcLang = urlencode( $srcLang );

	if (defined("_BING_APIKEY")) {
		//echo "BING";
		$trans = translate_requestCurl_bing(_BING_APIKEY, $text, $srcLang, $destLang);
	} else {
		//echo "GOOGLE";
		$trans = translate_requestCurl( "v=1.0&key="._GOOGLETRANSLATE_APIKEY."&q=".rawurlencode($text)."&langpair=$srcLang%7C$destLang" );
	}

	$ltr = lang_dir($destLang, 'ltr','rtl');
	
	return "<div dir='$ltr' lang='$destLang'>$trans</div>";
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
		// echo "EN BASE";
		return $trad;
	} else {
		 //echo "NOUVEAU";
		$trad = traduire_texte( $text, $destLang, $srcLang );
		if ($trad) {
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