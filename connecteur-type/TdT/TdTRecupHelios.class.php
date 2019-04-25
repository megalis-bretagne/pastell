<?php

require_once (__DIR__."/../../module/helios-generique/lib/HeliosGeneriquePESAcquit.class.php");


class TdTRecupHelios extends ConnecteurTypeActionExecutor {

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function go(){

	    /** @var TdtConnecteur $tdT */
		$tdT = $this->getConnecteur("TdT");

		$tdt_error = $this->getMappingValue('tdt-error');
		$info_tdt_action = $this->getMappingValue('info-tdt');
		$has_reponse_element = $this->getMappingValue('pes_has_reponse');
		$fichier_reponse_element = $this->getMappingValue('pes_acquit');


		$tedetis_transaction_id = $this->getDonneesFormulaire()->get($this->getMappingValue('pes_tedetis_transaction_id'));
		
		$actionCreator = $this->getActionCreator();
		if ( ! $tedetis_transaction_id){
            $message="Une erreur est survenu lors de l'envoi à ".$tdT->getLogicielName();
            $this->setLastMessage($message);
            $actionCreator->addAction($this->id_e,0,$tdt_error,$message);
            $this->notify($tdt_error, $this->type,$message);
            return false;
		}
	
		$status = $tdT->getStatusHelios($tedetis_transaction_id);
		
		if ($status === false){
			$this->setLastMessage($tdT->getLastError());
			return false;
		} 
		
		$status_info = $tdT->getStatusInfo($status);
		
		if ($status == TdtConnecteur::STATUS_ERREUR){
			$message = "Transaction en erreur sur le TdT";
			if ($tdT->getLastReponseFile()){
                $xml = simplexml_load_string($tdT->getLastReponseFile());
                if ($xml){
                    $message = utf8_decode($xml->{'message'});
                }
            }
			$this->setLastMessage($message);
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,$tdt_error,$message);
			$this->notify($tdt_error, $this->type,$message);
			return false;
		}
		
		$next_message = "La transaction est dans l'état : $status_info ($status) ";

		if (in_array($status,array(TdtConnecteur::STATUS_ACQUITTEMENT_RECU,TdtConnecteur::STATUS_REFUSE,TdtConnecteur::STATUS_HELIOS_INFO))){
			$next_action = $info_tdt_action;
			$next_message = "Une réponse est disponible pour ce fichier PES";
			$this->getDonneesFormulaire()->setData($has_reponse_element,true);
			$retour = $tdT->getFichierRetour($tedetis_transaction_id);
			$this->getDonneesFormulaire()->addFileFromData($fichier_reponse_element, "retour.xml", $retour);
			$actionCreator->addAction($this->id_e,0,$next_action,$next_message);
			$this->notify($next_action, $this->type,$next_message);
			$this->recupPESAcquitInfo();
		}
		$this->setLastMessage( $next_message );
		return true;
	}

    public function recupPESAcquitInfo(){
        $heliosMipihPESAcquit = new HeliosGeneriquePESAcquit();
        $etat_ack = $heliosMipihPESAcquit->getEtatAck($this->getDonneesFormulaire()->getFilePath($this->getMappingValue('pes_acquit')))?1:2;
        $this->getDonneesFormulaire()->setData($this->getMappingValue('pes_etat_ack'),$etat_ack);
    }

}
