<?php

//Construit ou recrée le role admin. Fixe les droits sur les entités
require_once( __DIR__ . "/../init.php");

$objectInstancier->AdminControler->fixDroit();