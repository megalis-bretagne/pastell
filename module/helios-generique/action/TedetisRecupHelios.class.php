<?php

require_once (__DIR__."/../lib/HeliosGeneriquePESAcquit.class.php");


class TedetisRecupHelios extends ActionExecutor {

	public function go(){

	    /** @var TdtConnecteur $tdT */
		$tdT = $this->getConnecteur("TdT"); 
				
		$tedetis_transaction_id = $this->getDonneesFormulaire()->get('tedetis_transaction_id');
		
		$actionCreator = $this->getActionCreator();
		if ( ! $tedetis_transaction_id){
			$actionCreator->addAction($this->id_e,0,'tdt-error',"Une erreur est survenu lors de l'envoie à ".$tdT->getLogicielName());
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
			$this->setLastMessage($message);
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'tdt-error',$message);
			$this->notify('tdt-error', $this->type,$message);
			return false;
		}
		
		$next_message = "La transaction est dans l'état : $status_info ($status) ";
        $next_action = "";
		if ($status == TdtConnecteur::STATUS_ACQUITTEMENT_RECU) {
			$next_action = 'acquiter-tdt';
			$next_message = "Un acquittement PES a été recu";
		}
		if ($status == TdtConnecteur::STATUS_REFUSE){
			$next_action = 'refus-tdt';
			$next_message = "Le fichier PES a été refusé";
		}
		if ($status == TdtConnecteur::STATUS_HELIOS_INFO){
			$next_action = 'info-tdt';
			$next_message = "Une réponse est disponible pour ce fichier PES";
		}
		if (in_array($status,array(TdtConnecteur::STATUS_ACQUITTEMENT_RECU,TdtConnecteur::STATUS_REFUSE,TdtConnecteur::STATUS_HELIOS_INFO))){
			$this->getDonneesFormulaire()->setData('has_reponse',true);
			$retour = $tdT->getFichierRetour($tedetis_transaction_id);
			$this->getDonneesFormulaire()->addFileFromData('fichier_reponse', "retour.xml", $retour);
			$actionCreator->addAction($this->id_e,0,$next_action,$next_message);
			$this->notify('acquiter-tdt', $this->type,$next_message);
			$this->recup_pes_acquit_info();
		}
		$this->setLastMessage( $next_message );
		return true;
	}

    public function recup_pes_acquit_info(){
        $heliosMipihPESAcquit = new HeliosGeneriquePESAcquit();
        $etat_ack = $heliosMipihPESAcquit->getEtatAck($this->getDonneesFormulaire()->getFilePath('fichier_reponse'))?1:2;
        $this->getDonneesFormulaire()->setData('etat_ack',$etat_ack);
    }

}
