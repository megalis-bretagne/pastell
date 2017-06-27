<?php

class SignatureRecuperation extends ConnecteurTypeActionExecutor {

	public function go(){
		/** @var SignatureConnecteur $signature */
		$signature = $this->getConnecteur('signature');
		if (!$signature){
			throw new Exception("Il n'y a pas de connecteur de signature défini");
		}

		$donneesFormulaire = $this->getDonneesFormulaire();
		$document_element = $this->getMappingValue('document');
		$titre_element = $this->getMappingValue('titre');
		$has_historique_element = $this->getMappingValue('has_historique');
		$iparapheur_historique_element = $this->getMappingValue('iparapheur_historique');

		$filename = $donneesFormulaire->getFileName($document_element);
		$dossierID = $signature->getDossierID($donneesFormulaire->get($titre_element),$filename);
		$all_historique = false;
		try {
			$all_historique = $signature->getAllHistoriqueInfo($dossierID);
		} catch(Exception $e){
			$this->throwError($signature, $e->getMessage());
		}

		if (! $all_historique){
			$message = "La connexion avec le iParapheur a échoué : " . $signature->getLastError();
			$this->throwError($signature, $message);
		}

		$array2XML = new Array2XML();
		$historique_xml = $array2XML->getXML('iparapheur_historique',json_decode(json_encode($all_historique),true));

		$donneesFormulaire->setData($has_historique_element,true);
		$donneesFormulaire->addFileFromData($iparapheur_historique_element,"iparapheur_historique.xml",$historique_xml);

		$result = $signature->getLastHistorique($all_historique);

		if (strstr($result,"[Archive]")){
			return $this->retrieveDossier($dossierID);
		} else if (strstr($result,"[RejetVisa]") || strstr($result,"[RejetSignataire]")){
			$this->rejeteDossier($result);
			$signature->effacerDossierRejete($dossierID);
		} else {
			$this->throwError($signature, $result);
		}
		$this->setLastMessage($result);
		return true;
	}

	public function throwError(SignatureConnecteur $signature,$message){
		$nb_jour_max = $signature->getNbJourMaxInConnecteur();
		$lastAction = $this->getDocumentActionEntite()->getLastActionInfo($this->id_e,$this->id_d);
		$time_action = strtotime($lastAction['date']);

		if (time() - $time_action > $nb_jour_max * 86400){
			$message = "Aucune réponse disponible sur le parapheur depuis $nb_jour_max jours !";
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'erreur-verif-iparapheur',$message);
			$this->notify($this->action, $this->type,$message);
		}

		throw new Exception($message);
	}

	public function rejeteDossier($result){
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'rejet-iparapheur',"Le document a été rejeté dans le parapheur : $result");
	}

	public function retrieveDossier($dossierID){
		/** @var SignatureConnecteur $signature */
		$signature = $this->getConnecteur('signature');
		$donneesFormulaire = $this->getDonneesFormulaire();

		$has_signature_element = $this->getMappingValue('has_signature');
		$signature_element = $this->getMappingValue('signature');
		$document_element = $this->getMappingValue('document');
		$document_orignal_element = $this->getMappingValue('document_orignal');
		$bordereau_element = $this->getMappingValue('bordereau');

		$info = $signature->getSignature($dossierID);
		if (! $info ){
			$this->setLastMessage("La signature n'a pas pu être récupérée : " . $signature->getLastError());
			return false;
		}

		$donneesFormulaire->setData($has_signature_element,true);
		if ($info['signature']){
			$donneesFormulaire->addFileFromData($signature_element,"signature.zip",$info['signature']);
		}

		$document_original_name = $donneesFormulaire->getFileName($document_element);
		$document_original_data = $donneesFormulaire->getFileContent($document_element);
		$donneesFormulaire->addFileFromData($document_orignal_element, $document_original_name, $document_original_data);
		if (isset($info['document_signe']['document'])){
			$donneesFormulaire->addFileFromData($document_element,$document_original_name,$info['document_signe']['document']);
		}

		$donneesFormulaire->addFileFromData($bordereau_element,$info['nom_document'],$info['document']);

		$this->setLastMessage("La signature a été récupérée");

		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'recu-iparapheur',"La signature a été récupérée sur parapheur électronique");
		return true;
	}
}