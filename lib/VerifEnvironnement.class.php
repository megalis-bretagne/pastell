<?php
class VerifEnvironnement {
	
	private $last_error;
	
	public function getLastError(){
		return $this->last_error;
	}
	
	public function checkPHP(){
		return array("min_value" => "7.0","environnement_value" => phpversion());
	}
	
	public function checkExtension(){ 
		$extensionNeeded = array(
			"bcmath",
		    "curl",
			"fileinfo",
			"imap",
			"ldap",
			"mbstring",
            "openssl",
			"pdo",
			"pdo_mysql",
			"phar",
			"redis",
            "simplexml",
            "soap",
            "ssh2",
			"Zend OPcache",
            "zip",
		);
		$result = array();
		foreach($extensionNeeded as $extension){
			$result[$extension] = extension_loaded($extension);
		}
		return $result;
	}

	public function checkClasses(){
		$classesNeedded = array('Cron\CronExpression');
		$result = array();
		foreach($classesNeedded as $class){
			$result[$class] = class_exists($class);
		}
		return $result;
	}
	
	public function checkWorkspace(){
		if (! defined("WORKSPACE_PATH")){
			$this->last_error = "WORKSPACE_PATH n'est pas défini"; 
			return false;
		}
		if (! is_readable(WORKSPACE_PATH)) {
			$this->last_error = WORKSPACE_PATH ." n'est pas accessible en lecture"; 
			return false;
		}
		if (! is_writable(WORKSPACE_PATH)) {
			$this->last_error = WORKSPACE_PATH ." n'est pas accessible en écriture"; 
			return false;
		}
		return true;
	}
	
	public function checkCommande(array $allCommande) {
		$result = array();
		foreach($allCommande as $commande) {
			$result[$commande] = exec("which $commande");
		}
		return $result;
	}

	public function checkRedis(){
	    if (! class_exists("Redis")){
	        $this->last_error = "L'extension Redis n'est pas installée";
	        return false;
        }
        if (! defined("REDIS_SERVER") || empty(REDIS_SERVER)){
	        $this->last_error = "Pastell n'est pas configuré pour utiliser REDIS";
            return false;
	    }

        $redis = new Redis();
        if (!$redis->connect(REDIS_SERVER, REDIS_PORT)){
            $this->last_error = "Erreur lors de la connexion au serveur Redis : " . $redis->getLastError();
            return false;
        }
        return true;
    }
	
}