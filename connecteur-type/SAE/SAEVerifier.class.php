<?php

class SAEVerifier extends ConnecteurTypeActionExecutor {

    const SAE_TRANSFERT_ID = 'sae_transfert_id';
    const AR_SAE = 'ar_sae';
	const ACTION_NAME_RECU = 'ar-recu-sae';
    const ACTION_NAME_ERROR = 'verif-sae-erreur';

    /**
     * @return bool
     * @throws Exception
     */
    public function go(){
        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');
        $donneesFormulaire = $this->getDonneesFormulaire();

        $sae_transfert_id = $this->getMappingValue(self::SAE_TRANSFERT_ID);
        $ar_sae = $this->getMappingValue(self::AR_SAE);
		$action_name_error = $this->getMappingValue(self::ACTION_NAME_ERROR);
		$action_name_recu = $this->getMappingValue(self::ACTION_NAME_RECU);

        $id_transfert = $donneesFormulaire->get($sae_transfert_id);

        try {
			$aknowledgement_content = $sae->getAcuseReception($id_transfert);
		} catch (UnrecoverableException $e){
        	$this->changeAction($action_name_error,"Erreur irrécupérable : " .$e->getMessage());
        	throw $e;
		}

        $donneesFormulaire->addFileFromData($ar_sae,'ar.xml',$aknowledgement_content);

        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xml = $simpleXMLWrapper->loadString($aknowledgement_content);

        $message = "Récupération de l'accusé de réception : ".strval($xml->getName()) . " - " . strval($xml->{'Comment'});

        $this->changeAction($action_name_recu,$message);
        $this->notify($action_name_recu, $this->type,$message);
        return true;
    }

}