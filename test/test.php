<?php 
require_once( __DIR__ . "/../web/init.php");

$test = <<<"TEST"
- l'�cole est finie !
- 35
TEST;

$result = spyc_load($test);
print_r($result);
