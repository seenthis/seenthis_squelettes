<?
function translate_requestCurl($url)
{
	$url_page = "https://ajax.googleapis.com/ajax/services/language/translate";
	
	$parameters = str_replace("$url_page?", "", $url);
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
		return $body;
}

function traduire_texte( $text, $destLang = 'fr', $srcLang = 'en' ) {

	$text = rawurlencode( $text );
	$destLang = urlencode( $destLang );
	$srcLang = urlencode( $srcLang );
	$key = "AIzaSyAWeaiTeH9QYP4qF_swOvqFQZ2wMy6nQ98";
		
	//$trans = translate_requestCurl( "https://www.googleapis.com/language/translate/v2?key=AIzaSyAWeaiTeH9QYP4qF_swOvqFQZ2wMy6nQ98&q=$text&source=$srcLang&target=$destLang" );
	$trans = translate_requestCurl( "https://ajax.googleapis.com/ajax/services/language/translate?v=1.0&key=AIzaSyAWeaiTeH9QYP4qF_swOvqFQZ2wMy6nQ98&q=$text&langpair=$srcLang%7C$destLang" );
	//https://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q=Hello,%20my%20friend!&langpair=en%7Ces
	
	$json = json_decode( $trans, true );

	
	if (isset($json["error"])) return false;
//	return urldecode($json["data"]["translations"][0]["translatedText"]);
	return urldecode($json["responseData"]["translatedText"]);
	
}

function traduire($text, $destLang = 'fr', $srcLang = 'en') {
	$text = mb_substr($text, 0, 4500, "UTF-8");
	
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