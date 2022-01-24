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

if (! function_exists('pastell_autoload')) {
    //PHPUnit est incompatible avec cette fonction d'autoload (warning + lancement d'exception)
    //Note : c'est un peu à la one-again, il faudrait sans doute refactorer cette fonction pour qu'elle
    //fonctionne dans tous les cas.
    function pastell_autoload($class_name)
    {
        $include = $class_name . '.class.php';
        @ $result = include($include);
        if (! $result) {
            return false;
        }
        return true;
    }
}

spl_autoload_register('pastell_autoload');


if (php_sapi_name() != "cli") {
    ini_set("session.cookie_httponly", 1);
    session_start();
}
