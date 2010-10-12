<?php

class ActionCreator {
	
	private $sqlQuery;
	private $journal;
	private $id_d;
	
	private $lastAction;
	private $id_a;
	
	public function __construct(SQLQuery $sqlQuery,Journal $journal, $id_d){
		$this->sqlQuery = $sqlQuery;
		$this->journal = $journal;
		$this->id_d = $id_d;	
	}
	
	public function addAction($id_e,$id_u,$action,$message_journal){
		$now = date(Date::DATE_ISO);
		$this->lastAction = $action;
		
		$sql = "INSERT INTO document_action(id_d,date,action,id_e,id_u) VALUES (?,?,?,?,?)";
		$this->sqlQuery->query($sql,$this->id_d,$now,$action,$id_e,$id_u);
				
		$sql = " UPDATE document SET last_action=? WHERE id_d=?";
		$this->sqlQuery->query($sql,$action,$this->id_d);
		
		$sql = "UPDATE document_entite SET last_action=? , last_action_date=? WHERE id_d=? AND id_e=?";
		$this->sqlQuery->query($sql,$action,$now,$this->id_d,$id_e);
		
		$sql = "SELECT id_a FROM document_action WHERE id_d=? AND date=? AND action=? AND id_e=? AND id_u=?";
		$this->id_a =  $this->sqlQuery->fetchOneValue($sql,$this->id_d,$now,$action,$id_e,$id_u);
	
		$this->addToEntite($id_e,$message_journal);
	}
	
	public function addToEntite($id_e,$message_journal){
		assert('$this->id_a');
		
		$sql = "INSERT INTO document_action_entite (id_a,id_e) VALUES (?,?)";
		$this->sqlQuery->query($sql,$this->id_a,$id_e);
		
		$this->journal->add(Journal::DOCUMENT_ACTION,$id_e,$this->id_d,$this->lastAction,$message_journal);
	}
		
}