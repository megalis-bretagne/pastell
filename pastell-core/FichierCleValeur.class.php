<?php

require_once (__DIR__ . "/../ext/spyc.php");

/**
 * S'occupe des relations avec les fichiers YML enregistrant les données de documents 
 * C'est cette classe qui formate correctement les données avant de les enregistré au format YML 
 * et c'est elle qui récupère les données avant de les reformatés dans l'autre sens
 */
class FichierCleValeur {
	
	private $filePath;
	private $info;
	
	public function __construct($filePath){
		$this->filePath = $filePath;
		if ( ! file_exists($this->filePath)){
			return ;
		}
        $this->lockFile();
		$this->info = Spyc::YAMLLoad($this->filePath) ;
		$this->unlockFile();

		foreach($this->info as $field_name => $field_value){
			if (is_array($field_value)){
				foreach($field_value as $i => $value){
					$this->info[$field_name][$i] = $this->unescape($value);
				}
			} else {
				$this->info[$field_name] = $this->unescape($field_value);
			}
		}
	}
	
	//La conversion YML efface parfois des caractères lorsque ceux-ci peuvent être transformés en autre chose que des 
	//chaînes de charactères : +, +2, "false", etc.. 
	private function escape($string){
		return addslashes('"'.$string.'"');
	}
	
	private function unescape($string){
		$word = stripslashes($string);
		if(!$word){
			return "";
		}
		if ($word[0] == '"' && mb_substr($word,-1) == '"') {
			$word = mb_substr($word, 1);
			$word = mb_substr($word,0,-1);
		}
		return $word;
	}
	
	public function save(){
		$result = array();
        if ($this->info) {
            foreach($this->info as $field_name => $field_value){
                if (is_array($field_value)){
                    foreach($field_value as $i => $value){
                        $result[$field_name][$i] = $this->escape($value);
                    }
                } else {
                    $result[$field_name] = $this->escape($field_value);
                }
            }
        }
	
		$dump = Spyc::YAMLDump($result);


        $this->lockFile();
		$result = file_put_contents($this->filePath,$dump);
        $this->unlockFile();

        if ($result === false){
			throw new Exception("Impossible d'écrire dans le fichier {$this->filePath}");
		}
    }

    private function lockFile(){
        $this->setLock(LOCK_EX);
    }

    private function unlockFile(){
        $this->setLock(LOCK_UN);
    }

    private function setLock($operation){
        //For testing...
        if (substr($this->filePath,0,6) === 'vfs://'){
            return;
        }
        $fp = fopen( $this->filePath,"r");
        flock( $fp, $operation );
        fclose($fp);
    }
	
	public function getInfo(){		
		return $this->info;
	}
	
	public function get($key){
		if (isset($this->info[$key])){
			return $this->info[$key];
		} else {
			return false;
		}
	}
	
	public function set($key,$value){
		$this->info[$key] = $value;
	}
	
	public function exists($key){
		return isset($this->info[$key]);
	}
	
	public function getMulti($key,$num = 0){
		return $this->info[$key][$num];
	}
	
	public function setMulti($key,$value,$num = 0){
		$this->info[$key][$num] = $value;
	}
	
	public function addValue($key,$value){
		$this->info[$key][] = $value;
	}
	
	public function count($key){
		if (empty($this->info[$key])){
			return 0;
		}
		return count($this->info[$key]);
	}
	
	public function delete($key,$num){
		array_splice($this->info[$key],$num,1);
	}

	public function deleteField($key){
		unset($this->info[$key]);
	}
}


