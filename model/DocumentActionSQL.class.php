<?php 

class DocumentActionSQL extends SQL {

	public function add($id_d,$id_e,$id_u,$action){
		$now = date(Date::DATE_ISO);
		
		$sql = "INSERT INTO document_action(id_d,date,action,id_e,id_u) VALUES (?,?,?,?,?)";
		$this->query($sql,$id_d,$now,$action,$id_e,$id_u);
		
		$sql = "UPDATE document_entite SET last_action=? , last_action_date=? WHERE id_d=? AND id_e=?";
		$this->query($sql,$action,$now,$id_d,$id_e);
		
		$sql = "SELECT id_a FROM document_action WHERE id_d=? AND date=? AND action=? AND id_e=? AND id_u=?";
		$id_a =  $this->queryOne($sql,$id_d,$now,$action,$id_e,$id_u);
	
		return $id_a;
	}
	
	public function getLastActionInfo($id_d,$id_e){
		$sql = "SELECT * FROM document_action " .
				" WHERE id_d=? AND id_e=?" .
				" ORDER BY date DESC LIMIT 1";
		return $this->queryOne($sql,$id_d,$id_e);
	}
	
	public function updateDate($id_a){
		$sql = "UPDATE document_action SET date=now() WHERE id_a=?";
		$this->query($sql,$id_a);
	}
}