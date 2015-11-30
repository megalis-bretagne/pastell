<?php

/*
 * script d'intégration d'extensions à pastell
 * ex d'appel pour integrer les extensions actes et helios:
 * php api_edit_extension.php http://pastell.exemple.fr/api/ /data/extensions/ptl-actes /data/extensions/ptl-helios
 */

$i=0;
foreach ($argv as $arg) {	
	// exclu le premier argument (nom du script)
	if ($i==0) {
		$i++;
		continue; 
	}
	// url_api
	if ($i==1) {
		$i++;
		$url_api=$arg;
		continue;
	}	
	$post_data = array( 
		'path'=>$arg,
	);
	$url=$url_api."edit-extension.php";
	$retour=pastell_api($url, $post_data);

	$info = "Integration de l'extension ".$arg.": ";	
	if (!empty($retour["result"])) {
		$info .= $retour["result"]."\n";
	}
	elseif (!empty($retour["status"])) {
		$info .= $retour["status"].": ".$retour["error-message"]."\n";
	}
	else {
		$info .= "KO\n";
	}
	echo $info."\n";
}

// fonction curl
function pastell_api($url, $post_data) {
	$curl = curl_init();
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl,CURLOPT_URL,$url);
	curl_setopt($curl,CURLOPT_USERPWD,"admin:admin");	
	curl_setopt($curl, CURLOPT_POST,true);
	curl_setopt($curl, CURLOPT_POSTFIELDS,$post_data);
	$output = curl_exec($curl);
	if ($err = curl_error($curl)){ echo "Error : " . $err; }
	return json_decode($output,true);
}
