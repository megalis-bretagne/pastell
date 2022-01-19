<?php

/**
 * @var ObjectInstancier $objectInstancier
 */

//Construit ou recrée le role admin. Fixe les droits sur les entités
require_once __DIR__ . '/../init.php';

$objectInstancier->getInstance(AdminControler::class)->fixDroit();
