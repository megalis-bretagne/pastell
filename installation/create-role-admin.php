<?php

//Construit ou recr�e le role admin. Fixe les droits sur les entit�s
require_once( __DIR__ . "/../web/init.php");

$objectInstancier->AdminControler->fixDroit();