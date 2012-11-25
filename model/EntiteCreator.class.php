<?php

class EntiteCreator extends SQL {
	
	private $journal;
	
	public function __construct(SQLQuery $sqlQuery, Journal $journal){
		parent::__construct($sqlQuery);
		$this->journal = $journal;
	}
	
	public function edit($id_e,$siren,$denomination,$type,$entite_mere = 0,$id_e_centre_de_gestion = 0){
		$sql = "SELECT id_e FROM entite WHERE id_e=?";
		$id_e = $this->queryOne($sql,$id_e);
		if ($id_e){
			$this->update($id_e,$siren,$denomination,$type,$entite_mere,$id_e_centre_de_gestion);
		} else {
			$id_e = $this->create($siren,$denomination,$type,$entite_mere,$id_e_centre_de_gestion);
		}
		$this->updateAncetre($id_e,$entite_mere);		
		return $id_e;
	}
	
	private function create($siren,$denomination,$type,$entite_mere,$id_e_centre_de_gestion){
		$date_inscription = date(Date::DATE_ISO);
		$sql = "INSERT INTO entite(siren,denomination,type,entite_mere,date_inscription,centre_de_gestion) " . 
				" VALUES (?,?,?,?,?,?)";
		$this->query($sql,$siren,$denomination,$type,$entite_mere,$date_inscription,$id_e_centre_de_gestion);
		
		$sql = "SELECT id_e FROM entite WHERE siren = ? AND denomination=? AND type=? AND entite_mere=? AND date_inscription=?";
		$id_e =  $this->queryOne($sql,$siren,$denomination,$type,$entite_mere,$date_inscription);
	
		$this->journal->add(Journal::MODIFICATION_ENTITE,$id_e,0,"Cr��","");
		
		return $id_e;
	}
	
	private function update($id_e,$siren,$denomination,$type,$entite_mere,$id_e_centre_de_gestion){
		$sql = "UPDATE entite SET siren= ? , denomination=?,type=?,entite_mere = ?, centre_de_gestion=? " . 
				" WHERE id_e=?";
		$this->query($sql,$siren,$denomination,$type,$entite_mere,$id_e_centre_de_gestion,$id_e);
		$this->journal->add(Journal::MODIFICATION_ENTITE,$id_e,0,"Modifi�","");	
	}
	
	public function updateAncetre($id_e,$entite_ancetre){		
		$sql_delete = "DELETE FROM entite_ancetre WHERE id_e=?";
		$this->query($sql_delete,$id_e);
		$sql_insert = "INSERT INTO entite_ancetre(id_e_ancetre,id_e,niveau) VALUES (?,?,?)";
		$sql_select = "SELECT entite_mere FROM entite WHERE id_e=?";
		$niveau = 0;
		$this->query($sql_insert,$id_e,$id_e,$niveau++);
		while ($entite_ancetre != 0) {
			$this->query($sql_insert,$entite_ancetre,$id_e,$niveau++);
			$entite_ancetre = $this->queryOne($sql_select,$entite_ancetre);
		}
		$this->query($sql_insert,0,$id_e,$niveau);
	}
	
	public function setEtat($id_e,$etat){
		$sql = "UPDATE entite SET etat=? WHERE id_e=?";
		$this->query($sql,$etat,$id_e);
	}
	
}