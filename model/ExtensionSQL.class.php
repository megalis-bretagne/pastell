<?php 

class ExtensionSQL extends SQL {

	private static $has_cache = false;
	private static $cache;

	public function getAll(){
		if (! self::$has_cache) {
			$sql = "SELECT * FROM extension ORDER BY nom";
			self::$has_cache = true;
			self::$cache = $this->query($sql);
		}
		return self::$cache;
	}
	
	public function getInfo($id_e){
		$sql = "SELECT * FROM extension WHERE id_e=?";
		return $this->queryOne($sql,$id_e);
	}
	
	public function edit($id_e,$path){
		if($id_e){
			$sql = "UPDATE extension SET path=? WHERE id_e=?";
			$this->query($sql,$path,$id_e);
		} else {
			$sql = "INSERT INTO extension(path) VALUES (?)";
			$this->query($sql,$path);
		}
		self::$has_cache = false;
	}
	
	public function delete($id_e){
		$sql = "DELETE FROM extension WHERE id_e=?";
		$this->query($sql,$id_e);
		self::$has_cache = false;
	}
	
	public function getLastInsertId(){
		$sql = "SELECT LAST_INSERT_ID() FROM extension";
		return $this->queryOne($sql);
	}
}