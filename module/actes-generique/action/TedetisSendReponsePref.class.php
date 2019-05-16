<?php 

class TedetisSendReponsePref extends ActionExecutor {
	
	public function go(){
		/** @var S2low $tdT */
		$tdT = $this->getConnecteur("TdT"); 
		$id = $tdT->sendResponse( $this->getDonneesFormulaire());
		$message = "Réponse envoyée à la préfecture";
		$this->addActionOK($message);
		$this->setLastMessage($message);
		return true;
	}
	
	
}
