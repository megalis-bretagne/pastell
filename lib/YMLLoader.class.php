<?php

require_once (PASTELL_PATH . "/ext/spyc.php");

/**
 * Classe permettant de charger le contenu d'un fichier YAML pour le changé en tableau PHP.
 * Utilise également le système de cache APC pour ne pas appeller trop souvent la bibliothèque Spyc qui est très couteuse en temps
 * @author Eric Pommateau
 */
class YMLLoader {
	
	const CACHE_PREFIX = "yml_cache_"; 
	const CACHE_PREFIX_MD5 = "yml_cache_md5_";

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
	public function getArray($filename){
		ob_start();

		//include() est utilisé pour également bénéficié du cache APC (file_get_contents() n'utilise pas le cache)
		@ $err = include($filename);
		
		$content = ob_get_clean();
		if (!$err){
			return false;
		}
		$md5 = md5($content);
		$md5_cache = $this->memoryCache->fetch( self::CACHE_PREFIX_MD5 . $filename );
		if ($md5 == $md5_cache){
			return $this->memoryCache->fetch( self::CACHE_PREFIX . $filename);
		} 	
		
		$result = Spyc::YAMLLoadString($content);
		$this->memoryCache->store(self::CACHE_PREFIX . $filename,$result);
		$this->memoryCache->store(self::CACHE_PREFIX_MD5 .$filename,$md5);
		return $result;
	}
	
}