<?php
class Job {
	
	const NEXT_TRY_IN_MINUTES_DEFAULT = 1;
	
	const TYPE_DOCUMENT = 1;
	const TYPE_CONNECTEUR = 2;
	const TYPE_TRAITEMENT_LOT = 3;
	
	public $type;
	public $id_e;
	public $id_d;
	public $id_u;
	public $etat_source;
	public $etat_cible;
	public $next_try_in_minutes;
	public $last_message;
	public $lock;
	public $id_verrou;


	public function __construct(){
		$this->id_u = 0;
		$this->id_d = "";
		$this->id_e = 0;
		$this->id_ce = 0;
		
		$this->etat_cible = false;
		$this->next_try_in_minutes = self::NEXT_TRY_IN_MINUTES_DEFAULT;
		$this->id_verrou = "";
	}
	
	public function asString(){
		if ($this->type == self::TYPE_DOCUMENT){
			return "id_e: {$this->id_e} - id_d: {$this->id_d} - id_u: {$this->id_u} - source: {$this->etat_source} - cible: {$this->etat_cible}";
		}
	}
	
	public function isTypeOK(){
		return in_array($this->type, array(Job::TYPE_CONNECTEUR,Job::TYPE_DOCUMENT,self::TYPE_TRAITEMENT_LOT));
	}
	
}