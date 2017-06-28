<?php

class FournisseurCommandeReceptionParapheur extends ActionExecutor {

	public function go(){

	    /** @var SignatureConnecteur $signature */
		$signature = $this->getConnecteur('signature');
		if (!$signature){
			throw new Exception("Il n'y a pas de connecteur de signature défini");
		}

		$donneesFormulaire = $this->getDonneesFormulaire();

		$filename = $donneesFormulaire->getFileName('commande');
		$dossierID = $signature->getDossierID($donneesFormulaire->get('libelle'),$filename);
		try {
			$all_historique = $signature->getAllHistoriqueInfo($dossierID);
		} catch(Exception $e){
			$this->traitementErreur($signature, $e->getMessage());
		}

		if (! $all_historique){
			$message = "La connexion avec le iParapheur a échoué : " . $signature->getLastError();
			$this->traitementErreur($signature, $message);
		}

		$array2XML = new Array2XML();
		$historique_xml = $array2XML->getXML('iparapheur_historique',json_decode(json_encode($all_historique),true));

		$donneesFormulaire->setData('has_historique',true);
		$donneesFormulaire->addFileFromData('iparapheur_historique',"iparapheur_historique.xml",$historique_xml);

		$result = $signature->getLastHistorique($all_historique);

		if (strstr($result,"[Archive]")){
			return $this->retrieveDossier($dossierID);
		} else if (strstr($result,"[RejetVisa]") || strstr($result,"[RejetSignataire]")){
			$this->rejeteDossier($result);
			$signature->effacerDossierRejete($dossierID);
		} else {
			$this->traitementErreur($signature, $result);
		}
		$this->setLastMessage($result);
		return true;
	}

	public function traitementErreur(SignatureConnecteur $signature, $message){
		$nb_jour_max = $signature->getNbJourMaxInConnecteur();
		$lastAction = $this->getDocumentActionEntite()->getLastActionInfo($this->id_e,$this->id_d);
		$time_action = strtotime($lastAction['date']);
		if (time() - $time_action > $nb_jour_max * 86400){
			$message = "Aucune réponse disponible sur le parapheur depuis $nb_jour_max !";
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'erreur-verif-iparapheur',$message);
			$this->notify($this->action, $this->type,$message);
            throw new Exception($message);
		}
        $this->setLastMessage($message);
		return true;
	}

	public function rejeteDossier($result){
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'rejet-iparapheur',"Le document a été rejeté dans le parapheur : $result");
	}

	public function retrieveDossier($dossierID){
		$signature = $this->getConnecteur('signature');
		$donneesFormulaire = $this->getDonneesFormulaire();

		$info = $signature->getSignature($dossierID);
		if (! $info ){
			$this->setLastMessage("La signature n'a pas pu être récupérée : " . $signature->getLastError());
			return false;
		}

		$donneesFormulaire->setData('has_signature',true);
		if ($info['signature']){
			$donneesFormulaire->addFileFromData('signature',"signature.zip",$info['signature']);
		}

		$document_original_name = $donneesFormulaire->getFileName('commande');
		$document_original_data = $donneesFormulaire->getFileContent('commande');
		$donneesFormulaire->addFileFromData('document_orignal', $document_original_name, $document_original_data);
		if ($info['document_signe']['document']){
			$donneesFormulaire->addFileFromData('commande',$document_original_name,$info['document_signe']['document']);
		}

		$donneesFormulaire->addFileFromData('bordereau',$info['nom_document'],$info['document']);

		$this->setLastMessage("La signature a été récupérée");

		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'recu-iparapheur',"La signature a été récupérée sur parapheur électronique");
		return true;

	}

}
