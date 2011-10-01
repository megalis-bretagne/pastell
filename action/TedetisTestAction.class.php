<?php

require_once( PASTELL_PATH . "/lib/system/Tedetis.class.php");
require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");

class TedetisTestAction extends ActionExecutor {
	
	public function go(){
				
		$tedetis = new Tedetis($this->getDonneesFormulaire());
		
		$result = $tedetis->testConnexion();
		
		if (! $result){
			$this->setLastMessage("La connexion avec S�low a �chou� : " . $tedetis->getLastError());
			return false;
		}

		$this->setLastMessage("La connexion est r�ussie");
		return true;
	}
	
}