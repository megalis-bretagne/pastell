<?php

require_once __DIR__ . '/../../../init.php';

$pastellUpdater = $objectInstancier->getInstance(PastellUpdater::class);
$pastellUpdater->to301();