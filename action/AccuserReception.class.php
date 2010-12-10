<?php

require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");

class AccuserReception extends ActionExecutor {

	public function go(){
		$documentEntite = new DocumentEntite($this->getSQLQuery());
		$id_ged = $documentEntite->getEntiteWithRole($this->id_d,"editeur");
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,$this->action, "Vous avez accus� r�ception de ce message");
		$this->getActionCreator()->addToEntite($id_ged,"Un accus� de r�ception a �t� recu pour le document");
		
		$message = "Un accus� de r�ception a �t� recu pour le document  " . $this->id_d;
		$this->getNotificationMail()->notify($id_ged,$this->id_d,$this->action, $this->type,$message);


		$this->setLastMessage("L'accus� de r�ception a �t� envoy� a l'�meteur du message");
		return true;
	}
}