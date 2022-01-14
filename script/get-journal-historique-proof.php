<?php

require_once(__DIR__ . "/../init.php");

$id_j = get_argv(1);

$opensslTSWrapper = $objectInstancier->getInstance(OpensslTSWrapper::class);

$sql = "SELECT preuve FROM journal_historique WHERE id_j=?";

echo($opensslTSWrapper->getTimestampReplyString($objectInstancier->SQLQuery->queryOne($sql, $id_j)));
