<?php

class SAEValider extends ConnecteurTypeActionExecutor {

	const TRANSFER_IDENTIFIER = 'TransferIdentifier';
	const TRANSFER_REPLY_IDENTIFIER = 'TransferReplyIdentifier';
	const COMMENT = 'Comment';


	/**
     * @return bool
     * @throws Exception
     */
    public function go(){

		$sae_transfert_id_element = $this->getMappingValue('sae_transfert_id');
		$reply_sae_element = $this->getMappingValue('reply_sae');
		$url_archive_element = $this->getMappingValue('url_archive');
		$action_name_error_envoi = $this->getMappingValue('erreur-envoie-sae');
		$action_name_error_validation = $this->getMappingValue('validation-sae-erreur');
		$action_name_accepter = $this->getMappingValue( 'accepter-sae');
		$action_name_rejet = $this->getMappingValue('rejet-sae');
		$sae_atr_comment_element = $this->getMappingValue('sae_atr_comment');
		$sae_archival_identifier_element = $this->getMappingValue('sae_archival_identifier');


		/** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');

        $donneesFormulaire = $this->getDonneesFormulaire();

        $id_transfert = $donneesFormulaire->get($sae_transfert_id_element);

        if (!$id_transfert){
            $message = "Impossible de trouver l'identifiant du transfert";
            $this->setLastMessage($message);
            $this->getActionCreator()->addAction($this->id_e,$this->id_u,$action_name_error_envoi,$message);
            $this->notify($this->action, $this->type,$message);
            return false;
        }

		try {
			$atr_content = $sae->getReply($id_transfert);
		} catch (UnrecoverableException $e){
			$this->changeAction($action_name_error_validation,"Erreur irrécupérable : " .$e->getMessage());
			throw $e;
		}


        $donneesFormulaire->addFileFromData($reply_sae_element,'ATR_unknow.xml',$atr_content);


		$simpleXMLWrapper = new SimpleXMLWrapper();
		$xml = $simpleXMLWrapper->loadString($atr_content);

		if (empty($xml->{self::TRANSFER_IDENTIFIER})){
			throw new UnrecoverableException(
				sprintf(
					"Impossible de trouver l'identifiant du message (%s) reçu dans la réponse du SAE",
					self::TRANSFER_IDENTIFIER
				)
			);
		}

		if ($xml->{self::TRANSFER_IDENTIFIER} != $id_transfert){
			throw new UnrecoverableException(
				sprintf(
					"L'identifiant du transfert (%s) ne correspond pas à l'identifiant de la réponse du SAE (%s)",
					$id_transfert,
					$xml->{self::TRANSFER_IDENTIFIER}
				)
			);
		}

		if (empty($xml->{self::TRANSFER_REPLY_IDENTIFIER})){
			throw new UnrecoverableException("Impossible de trouver l'identifiant du la réponse du SAE ");
		}

		$atr_name = sprintf("%s.xml",$xml->{self::TRANSFER_REPLY_IDENTIFIER});
		$donneesFormulaire->addFileFromData($reply_sae_element,$atr_name,$atr_content);

		if ($xml->{self::COMMENT}){
			$donneesFormulaire->setData($sae_atr_comment_element,$xml->{self::COMMENT});
		}

        $nodeName = strval($xml->getName());

        if ($nodeName == 'ArchiveTransferAcceptance' || ($nodeName == 'ArchiveTransferReply' && (strval($xml->ReplyCode) == '000'))){

			$sae_archival_identifier = strval($xml->Archive->ArchivalAgencyArchiveIdentifier);
			$donneesFormulaire->setData($sae_archival_identifier_element, $sae_archival_identifier);
            $url = $sae->getURL($sae_archival_identifier);
            $donneesFormulaire->setData($url_archive_element, $url);
            $message = "La transaction a été acceptée par le SAE";
            $next_action = $action_name_accepter;

        } else {
            $message = "La transaction a été refusée par le SAE";
            $next_action = $action_name_rejet;
        }

        $this->getActionCreator()->addAction($this->id_e,$this->id_u,$next_action,$message);
        $this->notify($next_action, $this->type,$message);

        $this->setLastMessage($message);
        return true;
    }
}