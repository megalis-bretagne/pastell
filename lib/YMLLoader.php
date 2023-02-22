<?php

use Symfony\Component\Yaml\Yaml;

/**
 * Classe permettant de charger le contenu d'un fichier YAML pour le changé en tableau PHP.
 * Utilise également un système de cache
 *
 * @author Eric Pommateau
 */
class YMLLoader
{
    public const CACHE_PREFIX = "yml_cache_";
    public const CACHE_PREFIX_MTIME = "mtime_";

    /**
     * @var MemoryCache
     */
    private $memoryCache;

    public function __construct(MemoryCache $memoryCache)
    {
        $this->memoryCache = $memoryCache;
    }

    /**
     * Transforme un fichier YAML en tableau PHP
     * @param $filename string chemin d'un fichier YAML
     * @param int $ttl time to live du cache
     * @return array|bool false si une erreur se produit, le tableau issu du fichier sinon
     */
    public function getArray($filename, $ttl = 0)
    {
        if (! file_exists($filename)) {
            return false;
        }
        $mtime = filemtime($filename);
        $mtime_cache = $this->memoryCache->fetch(self::CACHE_PREFIX_MTIME . $filename);

        if ($mtime_cache && $mtime <= $mtime_cache) {
            //HIT
            $result = $this->memoryCache->fetch(self::CACHE_PREFIX . $filename);
            if ($result) {
                return $result;
            }
        }

        //MISS
        $handle = $this->lockFile($filename);
        $result = Yaml::parseFile($filename);
        //For compatibility
        if ($result === null) {
            $result = [];
        }
        $this->memoryCache->store(self::CACHE_PREFIX . $filename, $result, $ttl);
        $this->memoryCache->store(self::CACHE_PREFIX_MTIME . $filename, $mtime, $ttl);
        $this->unlockFile($handle);

        return $result;
    }

    /**
     * @param $filename
     * @param array $array
     * @throws Exception
     */
    public function saveArray($filename, array $array)
    {
        $handle = $this->lockFile($filename);
        $yml_content = Yaml::dump($array, 10);
        if (file_put_contents($filename, $yml_content) === false) {
            throw new Exception("Impossible d'écrire dans le fichier {$filename}");
        }
        $this->memoryCache->delete(self::CACHE_PREFIX . $filename);
        $this->memoryCache->delete(self::CACHE_PREFIX_MTIME . $filename);
        $this->unlockFile($handle);
    }

    private function lockFile($filename)
    {
        // Il y a un problème avec l'utilisation de flock et file_get_contents qui renvoi false au lieu de bloquer
        if (! file_exists($filename) || strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return null;
        }
        $handle = fopen($filename, "r");
        flock($handle, LOCK_EX);
        return $handle;
    }

    private function unlockFile($handle)
    {
        if (!$handle) {
            return;
        }
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
