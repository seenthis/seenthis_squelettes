<?php


function action_seenli() {
	$id_me = intval(_request("me"), 36);

	header("HTTP/1.1 303 See Other");
	header("Location: "._HTTPS."://"._HOST."/messages/$id_me");
	exit();

}

?>
