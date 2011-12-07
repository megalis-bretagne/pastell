<?php

require_once( PASTELL_PATH . "/lib/system/Asalae.class.php");
require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");

class SAETestGenerateSEDA extends ActionExecutor {
	
	public function go(){
		
		$donneesFormulaire = $this->getDonneesFormulaire();
		
		$asalae = new Asalae($donneesFormulaire);
		
		$result = $asalae->generateSEDA("Ceci est un test");
		
		if (! $result){
			$this->setLastMessage("Le test a �chou� : " . $asalae->getLastError());
			return false;
		}
		
		$donneesFormulaire->addFileFromData('sae_bordereau_test','bordereau.xml',$result);
		
		$this->setLastMessage("Le bordereau a �t� cr��");
		return true;
	}
	
}