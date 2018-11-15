<?php

class TedetisRecup extends ActionExecutor {

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function go(){
	    /** @var TdtConnecteur $tdT */
		$tdT = $this->getConnecteur("TdT"); 
		
		if (!$tdT){
			throw new Exception("Aucun Tdt disponible");
		}
		
		$tedetis_transaction_id = $this->getDonneesFormulaire()->get('tedetis_transaction_id');
		
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
			$message = "Transaction en erreur sur le TdT";
			$this->setLastMessage($message);
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'erreur-verif-tdt',$message);
			$this->notify('erreur-verif-tdt', $this->type,$message);
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
		
		
		
		$actionCreator->addAction($this->id_e,0,'acquiter-tdt',"L'acte a été acquitté par le contrôle de légalité");
		
		$infoDocument = $this->getDocument()->getInfo($this->id_d);
		$documentActionEntite = $this->getDocumentActionEntite();
		$infoUser = $documentActionEntite->getUserFromAction($this->id_e,$this->id_d,'send-tdt');
		$message = "L'acte « {$infoDocument['titre']} » télétransmis par {$infoUser['prenom']} {$infoUser['nom']} a été acquitté par le contrôle de légalité";
		
		$message .= "\n\nConsulter le détail de l'acte : " . SITE_BASE . "Document/detail?id_d={$this->id_d}&id_e={$this->id_e}";
		
		$donneesFormulaire = $this->getDonneesFormulaire();

		$titre_document = $infoDocument['titre'];
		$titre_document = strtr($titre_document,"/","_");

		if ($bordereau_data){
			$donneesFormulaire->setData('has_bordereau',true);
			$donneesFormulaire->addFileFromData('bordereau', $titre_document."-bordereau.pdf",$bordereau_data);
		}
		if ($aractes){
			$donneesFormulaire->addFileFromData('aractes', "ARActes.xml",$aractes);
		}
		if ($actes_tamponne){
			$donneesFormulaire->addFileFromData('acte_tamponne',$titre_document."-tamponne.pdf",$actes_tamponne);
		}
		if ($annexes_tamponnees_list){
		    $file_number = 0;
			foreach($annexes_tamponnees_list as $i => $annexe_tamponnee){
			    if (! $annexe_tamponnee) {
			        continue;
                }
				$num_document = $i + 1;
				$donneesFormulaire->addFileFromData(
				    'annexes_tamponnees',
					$titre_document."-annexe-tamponne-{$num_document}.pdf",
                    $annexe_tamponnee,
                    $file_number++
                );
			}
		}
		
		$donneesFormulaire->setData('date_ar', $tdT->getDateAR($tedetis_transaction_id));
		
		$this->notify('acquiter-tdt', $this->type,$message);
	
		$this->setLastMessage("L'acquittement du contrôle de légalité a été reçu.");
		return true;
	}

}
