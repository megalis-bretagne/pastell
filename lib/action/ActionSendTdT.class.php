<?php

class ActionSendTdT {
	
	private $sqlQuery;
	
	public function __construct(SQLQuery $sqlQuery){
		$this->sqlQuery = $sqlQuery;
	}
	
	public function go($id_d){
		
		//V�rifier que le document est envoyable
		
		//Rendre le document in�ditable
		//Utiliser l'API TdT et envoyer le document
		//Notification + journal des transactions
		
	}
	
	
	
}