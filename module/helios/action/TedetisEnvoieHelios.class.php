<?php

class TedetisEnvoieHelios  extends ActionExecutor {

	public function go(){
		$tdT = $this->getConnecteur("TdT"); 
		$tdT->postHelios($this->getDonneesFormulaire());
		$this->addActionOK("Le document a �t� envoy� au TdT");
		return true;			
	}
}