<?php

function action_api_dist() {

	$xml = false;

	if ($_SERVER['SERVER_PORT'] != 443) erreur_405("Please use https");
	$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
	if ($id_auteur < 1) erreur_405("Unknown user", 401);

	$method = $_SERVER["REQUEST_METHOD"];
	
		
	if ($method == "PUT") {
		// UPDATE
		
		$putdata = fopen("php://input", "r");
		
		/* Open a file for writing */
		/* Read the data 1 KB at a time
		   and write to the file */
		while ($data = fread($putdata, 1024))
			$xml .= $data;
		fclose($putdata);
		$xml = trim($xml);
	}
	else if ($method == "POST") {	
		$xml = trim($GLOBALS["HTTP_RAW_POST_DATA"]);

		$contenttype = $_SERVER["CONTENT_TYPE"];
		if (!preg_match(",application\/(atom\+)?xml,", $contenttype)) {
			erreur_405("Wrong content-type");
		}


	} else if ($method == "DELETE") {
		$url = $_SERVER["REQUEST_URI"];
		if (preg_match("/messages\/([0-9]+)$/", $url, $regs)) {
			$id_me = $regs[1];
			echo "Supprimer: $id_me";
		}
	} else if ($method == "GET") {
		$url = $_SERVER["REQUEST_URI"];
		if (preg_match("/messages\/([0-9]+)$/", $url, $regs)) {
			$id_me = $regs[1];
		} else {
			erreur_405("No message found");
		}
	}
	
	if ($xml) {
	
	
		$xml = preg_replace("/<entry[^>]*>/msU", "<entry xmlns='http://www.w3.org/2005/Atom' xmlns:thr='http://purl.org/syndication/thread/1.0'>", $xml);
	
		$res = new SimpleXMLElement($xml);
		$id_me = $res->id;
		$id_parent = 0;
		$summary = trim($res->summary);
		$content = trim($res->content);
		
		if (strlen($summary) > 0) $texte_message = $summary;
		else $texte_message = $content;
		
		
		if (strlen($texte_message) < 1) die ("No text");
		
		$reply = $res->xpath("thr:in-reply-to/@ref");
		if ($reply) $reply = $reply[0];
		if (preg_match("/message\:([0-9]+)/", $reply, $regs)) {
			$id_parent = $regs[1];
		}
		if (preg_match("/message\:([0-9]+)/", $id_me, $regs)) {
			$id_me = $regs[1];
		} else {
			$id_me = 0;
		}
		
		$ret = instance_me ($id_auteur, $texte_message,  $id_me, $id_parent);
		$id_me = $ret["id_me"];
		
		if (!$id_me) erreur_405("Unexpected error - not saved in base");		

		cache_me($id_me);

	}
	
	header("Content-type:application/atom+xml; charset=utf-8");	

	echo "<"."?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	echo microcache($id_me, "noisettes/atom_me");
	
	exit();
	
}



?>