<?php

require_once(__DIR__."/../S2low.class.php");

class TestConnexion extends ActionExecutor {
	
	public function go(){
		
		$connecteur_properties = $this->getConnecteurProperties();
		
		$s2low = new S2low($connecteur_properties); 
		$result = $s2low->testConnexion();
		
		if (! $result){
			$this->setLastMessage("La connexion avec ".$s2low->getLogicielName()." a �chou� : " . $s2low->getLastError());
			return false;
		}

		$this->setLastMessage("La connexion est r�ussie : " . $result);
		return true;
	}
	
}