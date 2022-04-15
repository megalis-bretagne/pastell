#! /usr/bin/php
<?php

// TODO à transformer en connecteur global

/**
 * @var ObjectInstancier $objectInstancier
 */

require_once __DIR__ . '/../init.php';

$objectInstancier->getInstance(NotificationMail::class)->sendDailyDigest();
