<?php

require_once( PASTELL_PATH . "/lib/system/Asalae.class.php");
require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");

class SAETestSEDA extends ActionExecutor {
	
	public function go(){
				
		$asalae = new Asalae($this->getDonneesFormulaire());
		
		$result = $asalae->sendArchive("test.txt","Ceci est un test","test de d�pot");
		
		if (! $result){
			$this->setLastMessage("Le test a �chou� : " . $asalae->getLastError());
			return false;
		}

		$this->setLastMessage("Un document de test a �t� envoy� au service d'archive");
		return true;
	}
	
}