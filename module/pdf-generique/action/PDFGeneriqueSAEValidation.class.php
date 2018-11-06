<?php

/** @deprecated */
class PDFGeneriqueSAEValidation extends ActionExecutor {
	
	public function go(){
		/** @var SAEConnecteur $sae */
		$sae = $this->getConnecteur('SAE');
		$sae_config = $this->getConnecteurConfigByType('SAE');
		
		$id_transfert = $this->getDonneesFormulaire()->get('sae_transfert_id');
		
		if (!$id_transfert){
			$message = "Impossible de trouver l'identifiant du transfert";
			$this->setLastMessage($message);
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'erreur-envoie-sae',$message);		
			$this->notify($this->action, $this->type,$message);													
			return false;
		}
		$validation = $sae->getReply($id_transfert);
		
		if (! $validation){
			if ($sae->getLastErrorCode() == 8){
				$max_delai_ar = $sae_config->get("max_delai_validation") * 24 * 60 * 60;
				$lastAction = $this->getDocumentActionEntite()->getLastAction($this->id_e,$this->id_d);
				$time_action = strtotime($lastAction['date']);
				if (time() - $time_action < $max_delai_ar){
					$this->setLastMessage("Le document n'a pas encore été traité");
					return false;
				}
			}
			
			$message = $sae->getLastError();
			$this->setLastMessage($message);
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'validation-sae-erreur',$message);	
			$this->notify($this->action, $this->type,$message);										
			return false;
		} 
		
		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->addFileFromData('reply_sae','reply.xml',$validation);			
		
		$xml = simplexml_load_string($validation);
		
		if (! $xml){
			throw new Exception("Impossible de lire le contenu de la réponse du SAE");
		}

		$message = strval($xml->ReplyCode) . " - " . strval($xml->Comment);
		
		$nodeName = strval($xml->getName());
		if ($nodeName == 'ArchiveTransferAcceptance' || ($nodeName == 'ArchiveTransferReply' && (strval($xml->ReplyCode) == '000'))){
			$url = $sae->getURL(strval($xml->Archive->ArchivalAgencyArchiveIdentifier));
			$donneesFormulaire->setData('url_archive', $url);
			$message = "La transaction a été acceptée par le SAE";
			$next_action = "accepter-sae";			
			
		} else {
			$message = "La transaction a été refusée par le SAE";
			$next_action = "rejet-sae";		
		}
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,$next_action,$message);	
		$this->notify($next_action, $this->type,$message);				
		
		$this->setLastMessage($message);
		return true;
	}
}