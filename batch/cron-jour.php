#! /usr/bin/php
<?php

/**
 * @var ObjectInstancier $objectInstancier
 */

require_once __DIR__ . '/../init.php';

$objectInstancier->getInstance(NotificationMail::class)->sendDailyDigest();
