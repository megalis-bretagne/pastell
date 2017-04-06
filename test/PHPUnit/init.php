<?php

define("TESTING_ENVIRONNEMENT",true);

//require_once __DIR__.'/../../ext/composer/vendor/autoload.php';

set_include_path(	__DIR__.'/../../ext/composer/vendor/' . PATH_SEPARATOR .
	get_include_path()
);



function pastell_autoload($class_name) {
	$result = @ include_once($class_name . '.class.php');
	if ( ! $result ){
		return false;
	}
	return true;
}



require_once 'PastellTestCase.class.php';

require_once "mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStream.php";

require_once(__DIR__."/../../init-no-db.php");

require_once(__DIR__."/PastellSimpleTestCase.class.php");

require_once(__DIR__."/../../lib/dbupdate/DatabaseUpdate.class.php");


$sqlQuery = new SQLQuery(BD_DSN_TEST,BD_USER_TEST,BD_PASS_TEST);

$database_file = PASTELL_PATH."/installation/pastell.bin";

$databaseUpdate = new DatabaseUpdate(file_get_contents($database_file),$sqlQuery);
$sqlCommand = $databaseUpdate->getDiff();

foreach($sqlCommand as $sql) {

	$sqlQuery->query($sql);
}
