<?php

require_once __DIR__ . '/../../../init.php';

use Pastell\Updater;

/** @var Updater $pastellUpdater */
$pastellUpdater = $objectInstancier->getInstance(Updater::class);
$pastellUpdater->to('3.0.2');
