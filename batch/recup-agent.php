#! /usr/bin/php
<?php
require_once( dirname(__FILE__) . "/../web/init.php");

if (! defined('AGENT_FILE_PATH')){
	echo "Impossible de trouver le chemin des fichiers agents (constante AGENT_FILE_PATH absente)\n";
	exit;
}

if (! is_writable(AGENT_FILE_PATH)){
	echo "Impossible d'�crire sur ".AGENT_FILE_PATH."\n";
	exit;
}

$dh = opendir(AGENT_FILE_PATH);

if ( ! $dh ){
	echo "Impossible d'ouvrir le r�pertoire ".AGENT_FILE_PATH."\n";
	exit;
}

$CSV = new CSV();
$agentSQL = new AgentSQL($sqlQuery);

$nb_file = 0;

while (($file = readdir($dh)) !== false) {
	$file_path = AGENT_FILE_PATH."/".$file;
	
	if (! preg_match("#([0-9]{9})\.csv#",$file,$matches)){
		echo "Fichier $file ignor�\n";
		continue;
	}
	
	$siren = $matches[1];
	
	$fileContent = $CSV->get($file_path);
	$agentSQL->clean($siren);

	$nb_agent = 0;
	foreach($fileContent as $col){
		if (count($col) != 14){
			continue;
		}
		$infoCollectivite['siren'] = $siren;
		$agentSQL->add($col,$infoCollectivite);
		$nb_agent++;
	}
	echo "Fichier $file : Mise � jour collectivit� $siren : $nb_agent import�\n";
	unlink($file_path);
	$nb_file++;
	
}

closedir($dh);
echo "$nb_file ont �t� trait�s\n";