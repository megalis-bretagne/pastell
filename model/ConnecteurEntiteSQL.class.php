<?php
class ConnecteurEntiteSQL extends SQL {
	
	public function getAll($id_e){
		$sql = "SELECT * FROM connecteur_entite WHERE id_e = ?";
		return $this->query($sql,$id_e);
	}
	
	public function getAllLocal(){
		$sql = "SELECT * FROM connecteur_entite WHERE id_e != 0";
		return $this->query($sql);
	}
	
	public function addConnecteur($id_e,$id_connecteur,$type,$libelle){
		$sql = "INSERT INTO connecteur_entite (id_e,id_connecteur,type,libelle) VALUES (?,?,?,?)";
		$this->query($sql,$id_e,$id_connecteur,$type,$libelle);
		
		$sql = "SELECT id_ce FROM connecteur_entite WHERE id_e=? AND id_connecteur=? AND type= ? AND libelle=? ORDER BY id_ce DESC LIMIT 1";
		return $this->queryOne($sql,$id_e,$id_connecteur,$type,$libelle);
		
	}
	
	public function getInfo($id_ce){
		$sql = "SELECT * FROM connecteur_entite WHERE id_ce = ?";
		return $this->queryOne($sql,$id_ce);
	}
	
	public function delete($id_ce){
		$sql = "DELETE FROM connecteur_entite WHERE id_ce=?";
		return $this->query($sql,$id_ce);
	}
	
	public function edit($id_ce,$libelle){
		$sql = "UPDATE connecteur_entite SET libelle=? WHERE id_ce=?";
		$this->query($sql,$libelle,$id_ce);
	}
	
	public function getDisponible($id_e,$type){
		$sql = "SELECT * FROM connecteur_entite " .
				" WHERE id_e=? AND type=?";
		return $this->query($sql,$id_e,$type);
	}
	
	public function getGlobal($id_connecteur){
		$sql = "SELECT id_ce FROM connecteur_entite WHERE id_connecteur = ? AND id_e=0";
		return $this->queryOne($sql,$id_connecteur);
	}
	
	public function getOne($id_connecteur){
		$sql = "SELECT id_ce FROM connecteur_entite WHERE  id_connecteur = ?";
		return $this->queryOne($sql,$id_connecteur);
	}
	
	public function getAllById($id_connecteur){
		$sql = "SELECT * FROM connecteur_entite WHERE id_connecteur = ?";
		return $this->query($sql,$id_connecteur);
	}
	
	public function getByType($id_e,$type){
		$sql = "SELECT * FROM connecteur_entite WHERE id_e=? AND type= ? ORDER BY libelle DESC";
		return $this->query($sql,$id_e,$type);
	}
}