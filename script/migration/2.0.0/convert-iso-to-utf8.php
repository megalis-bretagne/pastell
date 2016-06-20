<?php

require_once __DIR__."/../../../init.php";

if (count($argv) < 2){
	echo "Usage: {$argv[0]} file_or_directory\n";
	echo "{$argv[0]} convert a file or a directory (recursively) from iso-8859-15 to utf-8\n";
	echo "";
	exit;
}

function iconv_log($message){
	echo "$message\n";
}

$iconv = new Iconv();
$iconv->setLogingFunction('iconv_log');
$iconv->convert($argv[1]);


