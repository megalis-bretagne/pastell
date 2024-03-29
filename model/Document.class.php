<?php

class Document extends SQL {
	
	const MAX_ESSAI = 5;
	
	private $passwordGenerator;
	private static $cache;

    public function __construct(SQLQuery $sqlQuery,PasswordGenerator $passwordGenerator){
        parent::__construct($sqlQuery);
        $this->passwordGenerator = $passwordGenerator;
    }

	public function getNewId(){
		for ($i=0; $i<self::MAX_ESSAI; $i++){
			$id_d = $this->passwordGenerator->getPassword();
			$sql = "SELECT count(*) FROM document WHERE id_d=?";
			$nb = $this->queryOne($sql,$id_d);
			
			if ($nb == 0){
				return $id_d;
			}	
		}
		throw new Exception("Impossible de trouver un numéro de transaction");
	}
	
	public function save($id_d,$type){
		$sql = "INSERT INTO document(id_d,type,creation,modification) VALUES (?,?,now(),now())";
		$this->query($sql,$id_d,$type);
	}
	
	public function setTitre($id_d,$titre){
		$sql = "UPDATE document SET titre = ?,modification=now() WHERE id_d = ?";
		$this->query($sql,$titre,$id_d);
		unset(self::$cache[$id_d]);
	}
	
	public function getInfo($id_d){
		if (empty(self::$cache[$id_d])) {
			$sql = "SELECT * FROM document WHERE id_d = ? ";
			self::$cache[$id_d] =  $this->queryOne($sql, $id_d);
		}
		return self::$cache[$id_d];
	}
	
	public function getIdFromTitre($titre,$type){		
		$sql = "SELECT id_d FROM document WHERE titre=? AND type=?";
		return $this->queryOne($sql,$titre,$type);
	}
	
	public function getIdFromEntiteAndTitre($id_e,$titre,$type){
		$sql = "SELECT document.id_d FROM document " .
				" JOIN document_entite ON document.id_d=document_entite.id_d ".
				" WHERE id_e=? AND titre=? AND type=?";
		return $this->queryOne($sql,$id_e,$titre,$type);
	}
	
	public function delete($id_d){
		$sql = "DELETE FROM document WHERE id_d=?";
		$this->query($sql,$id_d);
		
		$sql = "DELETE dae.* FROM document_action_entite dae";
		$sql .= " INNER JOIN document_action da ON dae.id_a = da.id_a";
		$sql .= " WHERE da.id_d = ?";
		$this->query($sql,$id_d);
			
		
		$sql = "DELETE FROM document_action WHERE id_d=?";
		$this->query($sql,$id_d);
		$sql = "DELETE FROM document_entite WHERE id_d=?";
		$this->query($sql,$id_d);
		
		$sql = "DELETE FROM document_index WHERE id_d=?";
		$this->query($sql,$id_d);

        $sql = "DELETE FROM document_email WHERE id_d=?";
        $this->query($sql,$id_d);
	}
	
	public function getAllByType($type){
		$sql = "SELECT id_d,titre FROM document WHERE type=? ORDER BY creation";
		return $this->query($sql,$type);
	}
	
	public function fixModule($old_flux_name,$new_flux_name){
        self::clearCache();
		$sql = "UPDATE document SET type= ? WHERE type = ?";
		return $this->query($sql,$new_flux_name,$old_flux_name);
	}
	
	public function getAllType(){
		$sql = "SELECT distinct type FROM document";
		return $this->queryOneCol($sql);
	}
	public static function clearCache() {
		self::$cache = array();
	}
	
}