<?php

require_once __DIR__ . '/../../../init.php';

use Pastell\Updater;

/** @var Updater $pastellUpdate */
$pastellUpdater = $objectInstancier->getInstance(Updater::class);
$pastellUpdater->to('3.0.1');
