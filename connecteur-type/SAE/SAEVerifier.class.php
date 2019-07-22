<?php

class SAEVerifier extends ConnecteurTypeActionExecutor {

    const SAE_TRANSFERT_ID = 'sae_transfert_id';
    const AR_SAE = 'ar_sae';
	const ACTION_NAME_RECU = 'ar-recu-sae';
    const ACTION_NAME_ERROR = 'verif-sae-erreur';
    const SAE_ACK_COMMENT = 'sae_ack_comment';

    const MESSAGE_RECEIVED_IDENTIFIER = 'MessageReceivedIdentifier';
    const ACKNOWLEDGEMENT_IDENTIFIER = 'AcknowledgementIdentifier';
    const COMMENT = 'Comment';

    /**
     * @return bool
     * @throws Exception
     */
    public function go(){
        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');
        $donneesFormulaire = $this->getDonneesFormulaire();

        $sae_transfert_id_element = $this->getMappingValue(self::SAE_TRANSFERT_ID);
        $ar_sae = $this->getMappingValue(self::AR_SAE);
		$action_name_error = $this->getMappingValue(self::ACTION_NAME_ERROR);
		$action_name_recu = $this->getMappingValue(self::ACTION_NAME_RECU);
		$sae_ack_comment_element = $this->getMappingValue(self::SAE_ACK_COMMENT);

        $id_transfert = $donneesFormulaire->get($sae_transfert_id_element);

        try {
			$aknowledgement_content = $sae->getAcuseReception($id_transfert);
		} catch (UnrecoverableException $e){
        	$this->changeAction($action_name_error,"Erreur irrécupérable : " .$e->getMessage());
        	throw $e;
		}

        $donneesFormulaire->addFileFromData($ar_sae,'ACK_unknow.xml',$aknowledgement_content);

        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xml = $simpleXMLWrapper->loadString($aknowledgement_content);

        if (empty($xml->{self::MESSAGE_RECEIVED_IDENTIFIER})){
        	throw new UnrecoverableException(
        		sprintf(
        			"Impossible de trouver l'identifiant du message (%s) reçu dans l'accusé de reception",
					self::MESSAGE_RECEIVED_IDENTIFIER
				)
			);
		}

        if ($xml->{self::MESSAGE_RECEIVED_IDENTIFIER} != $id_transfert){
        	throw new UnrecoverableException(
        		sprintf(
        		"L'identifiant du transfert (%s) ne corresppond pas à l'identifiant de l'accusé de reception (%s)",
					$id_transfert,
				$xml->{self::MESSAGE_RECEIVED_IDENTIFIER}
				)
			);
		}

		if (empty($xml->{self::ACKNOWLEDGEMENT_IDENTIFIER})){
			throw new UnrecoverableException(
				sprintf(
					"Impossible de trouver l'identifiant du l'accusé de reception (%s)",
					self::ACKNOWLEDGEMENT_IDENTIFIER
				)
			);
		}

		$ack_name = sprintf("%s.xml",$xml->{self::ACKNOWLEDGEMENT_IDENTIFIER});
		$donneesFormulaire->addFileFromData($ar_sae,$ack_name,$aknowledgement_content);

		if ($xml->{self::COMMENT}){
			$donneesFormulaire->setData($sae_ack_comment_element,$xml->{self::COMMENT});
		}

		$message = "Récupération de l'accusé de réception : ".strval($xml->getName()) . " - " . strval($xml->{'Comment'});

        $this->changeAction($action_name_recu,$message);
        $this->notify($action_name_recu, $this->type,$message);
        return true;
    }

}