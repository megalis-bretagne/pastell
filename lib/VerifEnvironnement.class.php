<?php

class VerifEnvironnement
{
    private $last_error;

    public function getLastError()
    {
        return $this->last_error;
    }

    public function checkPHP()
    {
        return ["min_value" => "7.2","environnement_value" => phpversion()];
    }

    private function getExtensionsNedeed()
    {
        $composer = json_decode(file_get_contents(__DIR__ . "/../composer.json"), true);
        return
            array_map(
                function ($a) {
                    return mb_substr($a, 4);
                },
                array_filter(
                    array_keys($composer['require']),
                    function ($a) {
                        return strpos($a, "ext-") === 0;
                    }
                )
            );
    }

    public function checkExtension()
    {
        $extensionNeeded = $this->getExtensionsNedeed();
        if (($key = array_search("zend-opcache", $extensionNeeded, true)) !== false) {
            $extensionNeeded[$key] = "Zend OPcache";
        }
        // pcntl is never loaded outside cli mode
        if (PHP_SAPI !== 'cli' && $key = array_search('pcntl', $extensionNeeded, true)) {
            unset($extensionNeeded[$key]);
        }
        $result = [];
        foreach ($extensionNeeded as $extension) {
            $result[$extension] = extension_loaded($extension);
        }
        return $result;
    }

    public function checkWorkspace()
    {
        if (! defined("WORKSPACE_PATH")) {
            $this->last_error = "WORKSPACE_PATH n'est pas défini";
            return false;
        }
        if (! is_readable(WORKSPACE_PATH)) {
            $this->last_error = WORKSPACE_PATH . " n'est pas accessible en lecture";
            return false;
        }
        if (! is_writable(WORKSPACE_PATH)) {
            $this->last_error = WORKSPACE_PATH . " n'est pas accessible en écriture";
            return false;
        }
        return true;
    }

    public function checkCommande(array $allCommande)
    {
        $result = [];
        foreach ($allCommande as $commande) {
            $result[$commande] = exec("which $commande");
        }
        return $result;
    }

    public function checkRedis()
    {
        if (! class_exists("Redis")) {
            $this->last_error = "L'extension Redis n'est pas installée";
            return false;
        }
        if (! defined("REDIS_SERVER") || empty(REDIS_SERVER)) {
            $this->last_error = "Pastell n'est pas configuré pour utiliser REDIS";
            return false;
        }

        $redis = new Redis();
        if (!$redis->connect(REDIS_SERVER, REDIS_PORT)) {
            $this->last_error = "Erreur lors de la connexion au serveur Redis : " . $redis->getLastError();
            return false;
        }
        return true;
    }
}
