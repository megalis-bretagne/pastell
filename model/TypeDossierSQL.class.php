<?php

class TypeDossierSQL extends SQL {

	//TODO Actuellement la taille du type de dossier ne peut être supérieur à 32 caractère

	public function edit($id_t,$id_type_dossier){
		if($id_t){
			$sql = "UPDATE type_dossier SET id_type_dossier=? WHERE id_t=?";
			$this->query($sql,$id_type_dossier,$id_t);
		} else {
			$sql = "INSERT INTO type_dossier(id_type_dossier) VALUES (?)";
			$this->query($sql,$id_type_dossier);
			$id_t = $this->lastInsertId();
		}
		return $id_t;
	}

	public function getAll(){
		$sql = "SELECT * FROM type_dossier ORDER BY id_type_dossier";
		return $this->query($sql);
	}

	public function getInfo($id_t){
		$sql = "SELECT * FROM type_dossier WHERE id_t=?";
		return $this->queryOne($sql,$id_t);
	}

	public function delete($id_t){
		$sql = "DELETE FROM type_dossier WHERE id_t=?";
		$this->query($sql,$id_t);
	}

	public function getByIdTypeDossier($id_type_dossier){
		$sql = "SELECT id_t FROM type_dossier WHERE id_type_dossier = ?";
		return $this->queryOne($sql,$id_type_dossier);
	}

}