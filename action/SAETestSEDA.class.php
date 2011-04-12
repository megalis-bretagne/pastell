<?php

require_once( PASTELL_PATH . "/lib/system/Asalae.class.php");
require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");

class SAETestSEDA extends ActionExecutor {
	
	public function go(){
				
		$asalae = new Asalae($this->getDonneesFormulaire());
		
		$result = $asalae->testSEDA();
		
		if (! $result){
			$this->setLastMessage("Le test a �chou� : " . $asalae->getLastError());
			return false;
		}

		$this->setLastMessage("Le test est r�ussie : ".htmlentities(utf8_decode($result))."");
		return true;
	}
	
}