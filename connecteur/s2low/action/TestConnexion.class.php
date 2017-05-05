<?php

class TestConnexion extends ActionExecutor {
	
	public function go(){
		$s2low = $this->getMyConnecteur();
		$s2low->testConnexion();		
		$this->setLastMessage("La connexion est réussie");
		return true;
	}
	
}