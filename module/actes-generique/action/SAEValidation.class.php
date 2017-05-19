<?php

class SAEValidation extends ActionExecutor {
	
	public function go(){
        /** @var Asalae $sae */
        $sae = $this->getConnecteur('SAE');

		$id_transfert = $this->getDonneesFormulaire()->get('sae_transfert_id');
	
		try{
			$validation = $sae->getReply($id_transfert);
		} catch (Exception $e){
			$this->setLastMessage($e->getMessage());
			return false;
		}
		
		if (! $validation){
			if ($sae->getLastErrorCode() == 8){
				$this->setLastMessage("Le document n'a pas encore été traité");
				return false;
			}
			
			$message = $sae->getLastError();
			$this->setLastMessage($message);
			return false;
		} 
		
		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->addFileFromData('reply_sae','reply.xml',$validation);			
		
		$xml = simplexml_load_string($validation);

		if (! $xml){
			throw new Exception("Impossible de lire le contenu de la réponse du SAE");
		}

		
		$nodeName = strval($xml->getName());
        if ($nodeName == 'ArchiveTransferAcceptance' || ($nodeName == 'ArchiveTransferReply' && (strval($xml->{'ReplyCode'}) == '000'))){
            $url = $sae->getURL(strval($xml->{'Archive'}->{'ArchivalAgencyArchiveIdentifier'}));
            $donneesFormulaire->setData("has_archive",true);
            $donneesFormulaire->setData("url_archive",$url);
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