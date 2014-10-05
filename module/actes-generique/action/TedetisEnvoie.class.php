<?php
class TedetisEnvoie  extends ActionExecutor {

	public function go(){
		$tdT = $this->getConnecteur("TdT"); 
		$tdT->postActes($this->getDonneesFormulaire());		
		
		$tdtConfig = $this->getConnecteurConfigByType("TdT");
		if ($tdtConfig->get('authentication_for_teletransmisson')){
			$this->changeAction("document-transmis-tdt","Le document a �t� envoy� au TdT");
		} else {
			$this->addActionOK("Le document a �t� envoy� au contr�le de l�galit�");
		}
		return true;
	}
}