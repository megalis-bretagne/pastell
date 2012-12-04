<?php

require_once(__DIR__."/../WebGFC.class.php");

class TestConnexion extends ActionExecutor {
	
	public function go(){
		$webGFC = $this->getMyConnecteur();
		
		$result = $webGFC->echoTest("Test de connection");
		
		if (! $result){
			$this->setLastMessage("La connexion avec WebGFC a �chou� : " . $webGFC->getLastError());
			return false;
		}

		$this->setLastMessage("La connexion est r�ussie : " . $result);
		return true;
	}
	
}