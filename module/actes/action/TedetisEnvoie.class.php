<?php
class TedetisEnvoie  extends ActionExecutor {

	public function go(){
		$tdT = $this->getConnecteur("TdT"); 
		$tdT->postActes($this->getDonneesFormulaire());		
		$this->addActionOK("Le document a �t� envoy� au contr�le de l�galit�");
		return true;			
	}
}