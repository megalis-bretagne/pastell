<?php

class TdTRecupActe extends ConnecteurTypeActionExecutor {

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function go(){

		$tedetis_transaction_id_element = $this->getMappingValue('tedetis_transaction_id');
		$erreur_verif_tdt = $this->getMappingValue('erreur-verif-tdt');
		$acquiter_tdt = $this->getMappingValue('acquiter-tdt');
		$send_tdt = $this->getMappingValue('send-tdt');
		$numero_de_lacte_element = $this->getMappingValue('numero_de_lacte');
		$has_bordereau_element = $this->getMappingValue('has_bordereau');
		$bordereau_element = $this->getMappingValue('bordereau');
		$aractes_element = $this->getMappingValue('aractes');
		$arrete_element = $this->getMappingValue('arrete');
		$acte_tamponne_element = $this->getMappingValue('acte_tamponne');
		$autre_document_attache_element = $this->getMappingValue('autre_document_attache');
		$annexes_tamponnees_element = $this->getMappingValue('annexes_tamponnees');
		$date_ar_element = $this->getMappingValue('date_ar');


		/** @var TdtConnecteur $tdT */
		$tdT = $this->getConnecteur("TdT");

		if (!$tdT){
			throw new UnrecoverableException("Aucun Tdt disponible");
		}

		$tedetis_transaction_id = $this->getDonneesFormulaire()->get($tedetis_transaction_id_element);

		$actionCreator = $this->getActionCreator();
		if ( ! $tedetis_transaction_id){
			$message="Une erreur est survenu lors de l'envoi à ".$tdT->getLogicielName()." (tedetis_transaction_id non disponible)";
			$this->setLastMessage($message);
			$actionCreator->addAction($this->id_e,0,'tdt-error',$message);
			$this->notify('tdt-error', $this->type,$message);
			return false;
		}

		try {
			$status = $tdT->getStatus($tedetis_transaction_id);
		} catch (Exception $e) {
			$message = "Echec de la récupération des informations : " .  $e->getMessage();
			$this->setLastMessage($message);
			return false;
		}

		if ($status == TdtConnecteur::STATUS_ERREUR){
			$message = "Transaction en erreur sur le TdT : ".$tdT->getLastError();
			$this->setLastMessage($message);
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,$erreur_verif_tdt,$message);
			$this->notify($erreur_verif_tdt, $this->type,$message);
			return false;
		}

		if ($status != TdtConnecteur::STATUS_ACQUITTEMENT_RECU){
			$this->setLastMessage("La transaction a comme statut : " . TdtConnecteur::getStatusString($status));
			return true;
		}

		$aractes = $tdT->getARActes();
		$bordereau_data = $tdT->getBordereau($tedetis_transaction_id);
		$actes_tamponne = $tdT->getActeTamponne($tedetis_transaction_id);
		$annexes_tamponnees_list = $tdT->getAnnexesTamponnees($tedetis_transaction_id);



		$actionCreator->addAction($this->id_e,0,$acquiter_tdt,"L'acte a été acquitté par le contrôle de légalité");

		$infoDocument = $this->getDocument()->getInfo($this->id_d);
		$documentActionEntite = $this->getDocumentActionEntite();
		$infoUser = $documentActionEntite->getUserFromAction($this->id_e,$this->id_d,$send_tdt);
		$message = "L'acte « {$infoDocument['titre']} » télétransmis par {$infoUser['prenom']} {$infoUser['nom']} a été acquitté par le contrôle de légalité";

		$message .= "\n\nConsulter le détail de l'acte : " . SITE_BASE . "Document/detail?id_d={$this->id_d}&id_e={$this->id_e}";

		$donneesFormulaire = $this->getDonneesFormulaire();
		$numero_de_lacte = $donneesFormulaire->get($numero_de_lacte_element);

		if ($bordereau_data){
			$donneesFormulaire->setData($has_bordereau_element,true);
			$donneesFormulaire->addFileFromData($bordereau_element, $numero_de_lacte."-bordereau-tdt.pdf",$bordereau_data);
		}
		if ($aractes){
			$donneesFormulaire->addFileFromData($aractes_element, "$numero_de_lacte-ar-actes.xml",$aractes);
		}
		if ($actes_tamponne){
			$actes_original_filename = $donneesFormulaire->getFileNameWithoutExtension($arrete_element);
			$donneesFormulaire->addFileFromData($acte_tamponne_element,$actes_original_filename."-tampon.pdf",$actes_tamponne);
		}
		if ($annexes_tamponnees_list){
			$file_number = 0;
			foreach($annexes_tamponnees_list as $i => $annexe_tamponnee){
				if (! $annexe_tamponnee) {
					continue;
				}
				$annexe_filename = $donneesFormulaire->getFileNameWithoutExtension($autre_document_attache_element,$i);
				$donneesFormulaire->addFileFromData(
					$annexes_tamponnees_element,
					$annexe_filename."-tampon.pdf",
					$annexe_tamponnee,
					$file_number++
				);
			}
		}

		$donneesFormulaire->setData($date_ar_element, $tdT->getDateAR($tedetis_transaction_id));

		$this->notify($acquiter_tdt, $this->type,$message);

		$this->setLastMessage("L'acquittement du contrôle de légalité a été reçu.");
		return true;
	}

}
