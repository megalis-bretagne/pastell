<?php
require_once( __DIR__ . "/../web/init.php");

/** @var SQLQuery $sqlQuery */
$sqlQuery = $objectInstancier->getInstance('SQLQuery');


$date = date("Y-m-d H:i:s",strtotime("-2 months"));
$sql = "SELECT id_j FROM journal WHERE date<? ORDER BY date LIMIT 1000";

do {

	$id_j_list = $sqlQuery->queryOneCol($sql, $date);

	$sql_insert = "INSERT INTO journal_historique SELECT * FROM journal WHERE id_j=?";
	$sql_delete = "DELETE FROM journal WHERE id_j=?";

	foreach ($id_j_list as $id_j) {
		echo $id_j . "\n";
		$sqlQuery->query($sql_insert, $id_j);
		$sqlQuery->query($sql_delete, $id_j);
	}

} while($id_j_list);