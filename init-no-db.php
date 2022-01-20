<?php

require_once(__DIR__ . "/vendor/autoload.php");
require_once(__DIR__ . "/DefaultSettings.php");

if (!empty(SENTRY_DSN)) {
    Sentry\init([
        'dsn' => SENTRY_DSN,
        'environment' => SENTRY_ENVIRONMENT,
    ]);
}

set_include_path(
    __DIR__ . "/pastell-core/" . PATH_SEPARATOR .
    __DIR__ . "/lib/" . PATH_SEPARATOR .
    __DIR__ . "/lib/dbupdate/" .     PATH_SEPARATOR .
    __DIR__ . "/model" . PATH_SEPARATOR .
    __DIR__ . "/controler" . PATH_SEPARATOR .
    __DIR__ . "/connecteur-type" . PATH_SEPARATOR .
    __DIR__ . "/api" . PATH_SEPARATOR .
    __DIR__ . "/pastell-core/type-dossier/" . PATH_SEPARATOR .
    __DIR__ . "/action/" . PATH_SEPARATOR .
    get_include_path()
);

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


require_once(PASTELL_PATH . "/lib/MemoryCache.interface.php");
require_once(PASTELL_PATH . "/lib/util.php");
require_once(PASTELL_PATH . "/pastell-core/Connecteur.class.php");
