<?php


function action_seenli() {
	$me = _request("me");
	
	$id_me = intval("$me", 36);

	header("HTTP/1.1 303 See Other");
	header("Location:http://seenthis.net/messages/$id_me");
	exit();

}

?>
