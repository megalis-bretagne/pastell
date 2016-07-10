<?php 

require_once( __DIR__ . "/../init.php");

$sql = "UPDATE journal JOIN document ON journal.id_d=document.id_d SET journal.document_type=document.type";

$objectInstancier->SQLQuery->query($sql);