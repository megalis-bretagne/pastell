<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/DefaultSettings.php';
require_once __DIR__ . '/lib/util.php';

if (!empty(SENTRY_DSN)) {
    Sentry\init([
        'dsn' => SENTRY_DSN,
        'environment' => SENTRY_ENVIRONMENT,
    ]);
}

if (php_sapi_name() != "cli") {
    ini_set("session.cookie_httponly", 1);
    session_start();
}
