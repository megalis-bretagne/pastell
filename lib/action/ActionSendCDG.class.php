<?php


class ActionSendCDG {
	
	private $sqlQuery;
	
	public function __construct(SQLQuery $sqlQuery){
		$this->sqlQuery = $sqlQuery;
	}
	
	public function go($id_d){
		//R�cuperer le siren de la collectivit�
		//R�cuperer son centre de gestion
		
		//V�rifier que le document est envoyable
		
		//Rendre le document in�ditable
		//Mettre � jour les droits

		//Notification + journal des transactions
		
	}
	
	
	
}