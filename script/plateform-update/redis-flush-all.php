<?php

// @deprecated 3.0 use general-update.php

require_once __DIR__ . "/../../init.php";

echo "Le script {$argv[0]} est déprécié ! Merci d'utiliser general-update.php à la place\n";

$redisWrapper = $objectInstancier->getInstance(MemoryCache::class);

$redisWrapper->flushAll();
