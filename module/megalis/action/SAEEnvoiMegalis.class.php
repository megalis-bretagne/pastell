<?php

class SAEEnvoiMegalis extends ActionExecutor {
	
	public function go(){
		$sae = $this->getConnecteur('SAE');
		$sae_config = $this->getConnecteurConfigByType('SAE');
		$bordereau = $this->getDonneesFormulaire()->getFileContent('bordereau');

		if (! $bordereau){
			$message = $this->getDonneesFormulaire()->getLastError();
			$this->setLastMessage($message);
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'erreur-envoie-sae',$message);	
		}
		
		$archive_path = $this->getDonneesFormulaire()->getFilePath('fichier_attache');

		$result = $sae->sendArchive($bordereau,$archive_path,"ZIP");
		
		if (! $result){
			$message = "L'envoi de l'archive a �chou� : " . $sae->getLastError();
			$this->setLastMessage($message);
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'erreur-envoie-sae',$message);				
			return false;
		} 
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,$this->action,"Le document a �t� envoy� au SAE");
		
		$this->setLastMessage("La transaction � �t� envoy� au SAE ".$sae_config->get('wsdl'));
		return true;	
	}
	
}