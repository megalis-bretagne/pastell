<?php

require_once(__DIR__ . "/../../init.php");

$entiteSQL = $objectInstancier->getInstance(EntiteSQL::class);
$sql = "SELECT id_d FROM document_entite WHERE id_e=?";

foreach($entiteSQL->getAll() as $entite_info){
	$id_d_list = $sqlQuery->queryOneCol($sql,$entite_info['id_e']);
	$size = 0;
	foreach($id_d_list as $id_d){
		$a = $id_d[0];
		$b = $id_d[1];
		$all_file = glob(WORKSPACE_PATH."/{$a}/{$b}/{$id_d}*");

		array_walk($all_file,function($filepath) use (& $size){
			$size += filesize($filepath);
		});
	}

	echo "{$entite_info['id_e']};{$entite_info['denomination']};$size\n";

}




