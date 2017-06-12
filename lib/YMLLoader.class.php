<?php

require_once (PASTELL_PATH . "/ext/spyc.php");

/**
 * Classe permettant de charger le contenu d'un fichier YAML pour le changé en tableau PHP.
 * Utilise également un système de cache pour ne pas appeller trop souvent la bibliothèque Spyc
 * qui est très couteuse en temps
 *
 * @author Eric Pommateau
 */
class YMLLoader {
	
	const CACHE_PREFIX = "yml_cache_";
	const CACHE_PREFIX_MTIME = "mtime_";

	/**
	 * @var MemoryCache
	 */
	private $memoryCache;

	public function __construct(MemoryCache $memoryCache){
		$this->memoryCache = $memoryCache;
	}

	/**
	 * Transforme un fichier YAML en tableau PHP
	 * @param string $filename chemin d'un fichier YAML
	 * @return boolean|array false si une erreur se produit, le tableau issu du fichier sinon
	 */
	public function getArray($filename,$ttl = 0){
	    if (! file_exists($filename)){
	        return false;
        }
	    $mtime = filemtime($filename);
        $mtime_cache = $this->memoryCache->fetch( self::CACHE_PREFIX_MTIME . $filename );

        if ($mtime_cache && $mtime <= $mtime_cache){
            //HIT
            $result = $this->memoryCache->fetch( self::CACHE_PREFIX . $filename);
            if ($result){
                return $result;
            }
        }

        //MISS
        $this->lockFile($filename);
        $result = Spyc::YAMLLoad($filename);
        $this->memoryCache->store(self::CACHE_PREFIX . $filename,$result, $ttl);
        $this->memoryCache->store(self::CACHE_PREFIX_MTIME .$filename,$mtime,$ttl);
        $this->unlockFile($filename);

        return $result;
	}

	public function saveArray($filename, array $array){
        $this->lockFile($filename);
        $yml_content = Spyc::YAMLDump($array);
        if (file_put_contents($filename,$yml_content) === false){
            throw new Exception("Impossible d'écrire dans le fichier {$filename}");
        }
        $this->memoryCache->delete(self::CACHE_PREFIX . $filename);
        $this->memoryCache->delete(self::CACHE_PREFIX_MTIME .$filename);
        $this->unlockFile($filename);
    }

    private function lockFile($filename){
        $this->setLock($filename, LOCK_EX);
    }

    private function unlockFile($filename){
        $this->setLock($filename, LOCK_UN);
    }

    private function setLock($filename, $operation){
        //For testing...
        /*if (substr($filename,0,6) === 'vfs://'){
            return;
        }*/
        if (! file_exists($filename)){
            return;
        }
        $fp = fopen($filename,"r");
        flock( $fp, $operation );
        fclose($fp);
    }
	
}