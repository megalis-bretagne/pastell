<?php
require_once(dirname(__FILE__) . "/../../init.php");
require_once(PASTELL_PATH . "/lib/dbupdate/DatabaseUpdate.class.php");

$do = get_argv(1);

$databaseUpdate = new DatabaseUpdate(file_get_contents(DATABASE_FILE), $sqlQuery);
$sqlCommand = $databaseUpdate->getDiff();

foreach ($sqlCommand as $command) {
    echo "$command\n";
    if ($do == 'do') {
        $sqlQuery->query($command);
    }
}

exit($sqlCommand ? 1 : 0);
