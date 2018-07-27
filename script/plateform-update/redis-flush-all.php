<?php

require_once __DIR__."/../../init.php";

$redisWrapper = $objectInstancier->getInstance(MemoryCache::class);

$redisWrapper->flushAll();