<?php

class SignatureRecuperation extends ConnecteurTypeActionExecutor {

    const ACTION_NAME_RECU = 'recu-iparapheur';
    const ACTION_NAME_REJET = 'rejet-iparapheur';
    const ACTION_NAME_ERROR = 'erreur-verif-iparapheur';

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
        $has_signature_element = $this->getMappingValue('has_signature');
        $signature_element = $this->getMappingValue('signature');
        $document_orignal_element = $this->getMappingValue('document_original');
        $bordereau_element = $this->getMappingValue('bordereau');
        $annexe_element = $this->getMappingValue('autre_document_attache');
        $iparapheur_annexe_sortie_element = $this->getMappingValue('iparapheur_annexe_sortie');
        $iparapheur_dossier_id = $this->getMappingValue('iparapheur_dossier_id');


        if ($donneesFormulaire->getFormulaire()->getField($iparapheur_dossier_id)) {
            $dossierID = $donneesFormulaire->get($iparapheur_dossier_id);
        }
        else { // conservé pour compatibilité
            $filename = $donneesFormulaire->getFileName($document_element);
            $dossierID = $signature->getDossierID($donneesFormulaire->get($titre_element),$filename);
        }

        $erreur = false;
        $all_historique = array();
        try {
            $all_historique = $signature->getAllHistoriqueInfo($dossierID);
            if (! $all_historique){
                $erreur = "La connexion avec le iParapheur a échoué : " . $signature->getLastError();
            }
        } catch (Exception $e){
            $erreur = $e->getMessage();
        }

        if (! $erreur) {
            $array2XML = new Array2XML();
            $historique_xml = $array2XML->getXML($iparapheur_historique_element, json_decode(json_encode($all_historique), true));

            $donneesFormulaire->setData($has_historique_element,true);
            $donneesFormulaire->addFileFromData($iparapheur_historique_element, "iparapheur_historique.xml", $historique_xml);
            $result = $signature->getLastHistorique($all_historique);
        } else {
            $result = false;
        }

        if (strstr($result,"[Archive]")){
            return $this->retrieveDossier($dossierID, $has_signature_element, $signature_element, $document_element, $document_orignal_element, $annexe_element, $iparapheur_annexe_sortie_element, $bordereau_element);
        } else if (strstr($result,"[RejetVisa]") || strstr($result,"[RejetSignataire]")){
            $this->rejeteDossier($dossierID, $result, $bordereau_element);
            $this->setLastMessage($result);
            return true;
        }

        $nb_jour_max = $signature->getNbJourMaxInConnecteur();
        $lastAction = $this->getDocumentActionEntite()->getLastActionInfo($this->id_e,$this->id_d);
        $time_action = strtotime($lastAction['date']);
        if (time() - $time_action > $nb_jour_max * 86400){
            $erreur = "Aucune réponse disponible sur le parapheur depuis $nb_jour_max jours !";
            $this->getActionCreator()->addAction($this->id_e,$this->id_u,self::ACTION_NAME_ERROR,$erreur);
            $this->notify(self::ACTION_NAME_ERROR, $this->type,$erreur);
        }

        if (! $erreur){
            $this->setLastMessage($result);
            return true;
        }

        $this->setLastMessage($erreur);
        return false;

	}

	public function rejeteDossier($dossierID,$result,$bordereau_element){
        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');
        $donneesFormulaire = $this->getDonneesFormulaire();

        $info = $signature->getSignature($dossierID);
        if (! $info ){
            $this->setLastMessage("Le bordereau n'a pas pu être récupéré : " . $signature->getLastError());
            return false;
        }
        $donneesFormulaire->addFileFromData($bordereau_element,$info['nom_document'],$info['document']);
        $signature->effacerDossierRejete($dossierID);

        $message = "Le document a été rejeté dans le parapheur : $result";
        $this->getActionCreator()->addAction($this->id_e,$this->id_u,self::ACTION_NAME_REJET,$message);
        $this->notify($this->action, $this->type,$message);

	}

	public function retrieveDossier($dossierID,
                                    $has_signature_element,
                                    $signature_element,
                                    $document_element,
                                    $document_orignal_element,
                                    $annexe_element,
                                    $iparapheur_annexe_sortie_element,
                                    $bordereau_element
                                    ){
		/** @var IParapheur $signature */
		$signature = $this->getConnecteur('signature');
		$donneesFormulaire = $this->getDonneesFormulaire();

		$info = $signature->getSignature($dossierID,false);
		if (! $info ){
			$this->setLastMessage("La signature n'a pas pu être récupérée : " . $signature->getLastError());
			return false;
		}

		$donneesFormulaire->setData($has_signature_element,true);
		if ($info['signature']){
			$donneesFormulaire->addFileFromData($signature_element,"signature.zip",$info['signature']);
		}
        elseif ($info['document_signe']['document']){
            $document_original_name = $donneesFormulaire->getFileName($document_element);
            $document_original_data = $donneesFormulaire->getFileContent($document_element);
            $donneesFormulaire->addFileFromData($document_orignal_element, $document_original_name, $document_original_data);

            $filename = substr($donneesFormulaire->getFileName($document_element), 0, -4);
            $filename_signe = preg_replace("#[^a-zA-Z0-9_]#", "_", $filename)."_signe.pdf";
            $donneesFormulaire->addFileFromData($document_element,$filename_signe,$info['document_signe']['document']);
        }

        $output_annexe = $signature->getOutputAnnexe($info,$donneesFormulaire->getFileNumber($annexe_element));
        foreach ($output_annexe as $i => $annexe){
            $donneesFormulaire->addFileFromData($iparapheur_annexe_sortie_element,$annexe['nom_document'],$annexe['document'],$i);
        }

		$donneesFormulaire->addFileFromData($bordereau_element,$info['nom_document'],$info['document']);

        if (! $signature->archiver($dossierID)){
            throw new RecoverableException(
                "Impossible d'archiver la transaction sur le parapheur : " . $signature->getLastError()
            );
        }

        $this->setLastMessage("La signature a été récupérée");
        $this->notify(self::ACTION_NAME_RECU, $this->type,"La signature a été récupérée");
        $this->getActionCreator()->addAction($this->id_e,$this->id_u,self::ACTION_NAME_RECU,"La signature a été récupérée sur parapheur électronique");
        return true;
	}
}