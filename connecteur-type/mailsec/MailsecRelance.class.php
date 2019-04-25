<?php

class MailsecRelance extends ConnecteurTypeActionExecutor {

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function go(){

		$send_mailsec_action = $this->getMappingValue('send-mailsec');
		$reception_partielle_action =  $this->getMappingValue('reception-partielle');
		$prepare_renvoi_action =$this->getMappingValue('prepare-renvoi');
		$non_recu_action = $this->getMappingValue('non-recu');


		/** @var PdfGeneriqueRelanceConnecteur $pdfGeneriqueRelanceConnecteur */
		$pdfGeneriqueRelanceConnecteur = $this->getConnecteur('pdf-relance');
		if (! $pdfGeneriqueRelanceConnecteur){
			throw new UnrecoverableException("Aucun connecteur pdf-generique-suppression");
		}

		$last_action = $this->getDocumentActionEntite()->getLastAction($this->id_e,$this->id_d);
		$action_list = $this->getDocumentActionEntite()->getAction($this->id_e,$this->id_d);
		$date_send_mailsec = false;
		foreach ($action_list as $action_info){
			if ($action_info['action'] == $send_mailsec_action){
				$date_send_mailsec = $action_info['date'];
			}
		}
		if (! $date_send_mailsec){
			throw new UnrecoverableException("Impossible de trouver la date du passage à send-mailsec");
		}

		if (in_array($last_action,[$send_mailsec_action,$reception_partielle_action]) && $pdfGeneriqueRelanceConnecteur->mustRelance($date_send_mailsec)){
			$message = "Préparation du renvoi du document";
			$this->setLastMessage($message);
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,$prepare_renvoi_action,$message);
			return true;
		}

		if ($pdfGeneriqueRelanceConnecteur->mustGoToNextState($date_send_mailsec)){
			$this->setLastMessage("Le document passe en non reçu !");
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,$non_recu_action,"Le temps de récupération du document est écoulé");
			return true;

		}
		$message = "";
		if (in_array($last_action,[$send_mailsec_action,$reception_partielle_action])) {
			$date_relance = $pdfGeneriqueRelanceConnecteur->getDateRelance($date_send_mailsec);
			$message .= "Relance programmée le $date_relance<br/>";
		}
		$date_non_recu = $pdfGeneriqueRelanceConnecteur->getDateNextState($date_send_mailsec);
		$message .= "Mail défini comme non-reçu le $date_non_recu<br/>";


		$this->setLastMessage($message);
		return true;
	}

}