<?php

require_once( PASTELL_PATH . "/lib/Array2XML.class.php");

class IParapheurRecupHelios extends ActionExecutor {

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');

        return $signature->isFastSignature()
            ? $this->goFast()
            : $this->goIparapheur();
    }

    /**
     * @param SignatureConnecteur $signature
     * @param $message
     * @throws Exception
     */
    public function throwError(SignatureConnecteur $signature, $message){
		
		$this->verifNbJour($signature,$message);
		throw new Exception($message);
	}
	
	
	private function verifNbJour(SignatureConnecteur $signature,$message){
		$nb_jour_max = $signature->getNbJourMaxInConnecteur();
		
		$lastAction = $this->getDocumentActionEntite()->getLastActionInfo($this->id_e,$this->id_d);
		
		$time_action = strtotime($lastAction['date']);
		if (time() - $time_action > $nb_jour_max * 86400){
			$message = "Aucune réponse disponible sur le parapheur depuis $nb_jour_max jours !";
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'erreur-verif-iparapheur',$message);
			$this->notify($this->action, $this->type,$message);
		}
	}


    /**
     * @return bool
     * @throws RecoverableException
     * @throws Exception
     */
    private function goIparapheur(){
		
		if ($this->from_api == false){
			$this->getJournal()->add(Journal::DOCUMENT_ACTION,$this->id_e,$this->id_d,'verif-iparapheur',"Vérification du retour iparapheur");
		}

        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');
		if (!$signature){
			throw new Exception("Il n'y a pas de connecteur de signature défini");
		}
		
		$helios = $this->getDonneesFormulaire();
		
		$file_array = $helios->get('fichier_pes');
		$filename = $file_array[0];
		$dossierID = $signature->getDossierID($helios->get('objet'),$filename);
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
		
		
		$helios->setData('has_historique',true);
		$helios->addFileFromData('iparapheur_historique',"iparapheur_historique.xml",$historique_xml);
		
		$result = $signature->getLastHistorique($all_historique);
		
		if (strstr($result,"[Archive]")){
			return $this->retrieveDossier();
		} else if (strstr($result,"[RejetVisa]") || strstr($result,"[RejetSignataire]")){
            $this->rejeteDossier($dossierID,$result);
		} else {
			$this->verifNbJour($signature,$result);
			$this->setLastMessage($result);
			return false;

		}
		
		$this->setLastMessage($result);
		return true;			
	}

    /**
     * @param $dossierID
     * @param $result
     * @return bool
     * @throws Exception
     */
    public function rejeteDossier($dossierID, $result){
        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');
        $donneesFormulaire = $this->getDonneesFormulaire();

        $info = $signature->getSignature($dossierID);
        if (! $info ){
            $this->setLastMessage("Le bordereau n'a pas pu être récupéré : " . $signature->getLastError());
            return false;
        }
        $donneesFormulaire->addFileFromData('document_signe',$info['nom_document'],$info['document']);

        $signature->effacerDossierRejete($dossierID);

		$this->notify('rejet-iparapheur', $this->type,"Le document a été rejeté dans le parapheur : $result");
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'rejet-iparapheur',"Le document a été rejeté dans le parapheur : $result");
	}

	/**
	 * @return bool
	 * @throws Exception
	 * @throws RecoverableException
	 */
	public function retrieveDossier(){
        /** @var IParapheur $signature */
		$signature = $this->getConnecteur('signature');
		
		$helios = $this->getDonneesFormulaire();
		$file_array = $helios->get('fichier_pes');
		$filename = $file_array[0];
		
		$dossierID = $signature->getDossierID($helios->get('objet'),$filename);
		
		$info = $signature->getSignature($dossierID,false);
		if (! $info ){
			$this->setLastMessage("La signature n'a pas pu être récupérée : " . $signature->getLastError());
			return false;
		}
		
		$helios->setData('has_signature',true);
		if ($info['signature']){
			$helios->addFileFromData('fichier_pes_signe',$filename,$info['signature']);
		} else {
			$fichier_pes_path = $helios->getFilePath('fichier_pes',0);
			$fichier_pes_content = file_get_contents($fichier_pes_path);
			$helios->addFileFromData('fichier_pes_signe',$filename,$fichier_pes_content);
		}
		$helios->addFileFromData('document_signe',$info['nom_document'],$info['document']);
        if (! $signature->archiver($dossierID)){
            throw new RecoverableException(
                "Impossible d'archiver la transaction sur le parapheur : " . $signature->getLastError()
            );
        }

		$output_annexe = $signature->getOutputAnnexe($info,0);
		foreach ($output_annexe as $i => $annexe){
			$helios->addFileFromData('iparapheur_annexe_sortie',$annexe['nom_document'],$annexe['document'],$i);
		}

		$this->setLastMessage("La signature a été récupérée");
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'recu-iparapheur',"La signature a été récupérée sur parapheur électronique");
		$this->notify('recu-iparapheur', $this->type,"La signature a été récupérée sur parapheur électronique");
		return true;
		
	}

    /**
     * @throws Exception
     */
    private function goFast()
    {
        /** @var FastParapheur $signature */
        $signature = $this->getConnecteur('signature');
        $helios = $this->getDonneesFormulaire();

        $documentId = $helios->get('iparapheur_dossier_id');
        $history = $this->getFileHistory($signature, $documentId);

        $array2XML = new Array2XML();
        $xmlHistory = $array2XML->getXML('iparapheur_historique', json_decode(json_encode($history), true));
        $helios->setData('has_historique', true);
        $helios->addFileFromData('iparapheur_historique', "history.xml", $xmlHistory);

        $lastDocumentHistory = $signature->getLastHistorique($history);
        if (strstr($lastDocumentHistory, "[Classé]")) {
            $this->retrieveFile($signature, $helios, $documentId);
        } else {
            $this->verifNbJour($signature, $lastDocumentHistory);
            $this->setLastMessage($lastDocumentHistory);
            return false;
        }

        $this->setLastMessage($lastDocumentHistory);
        return true;
    }

    /**
     * @param FastParapheur $signature
     * @param string $documentId
     * @return bool
     * @throws Exception
     */
    private function getFileHistory(FastParapheur $signature, string $documentId)
    {
        try {
            $history = $signature->getAllHistoriqueInfo($documentId);
        } catch (Exception $e) {
            $this->throwError($signature, $e->getMessage());
        }

        if (!$history) {
            $message = "La connexion avec le parapheur a échouée : " . $signature->getLastError();
            throw new Exception($message);
        }

        return $history;
    }

    /**
     * @param FastParapheur $signature
     * @param DonneesFormulaire $helios
     * @param string $documentId
     * @return bool
     * @throws Exception
     */
    private function retrieveFile(FastParapheur $signature, DonneesFormulaire $helios, string $documentId)
    {
        $signedFile = $signature->getSignature($documentId);
        if (!$signedFile) {
            $this->setLastMessage("La signature n'a pas pu être récupérée : " . $signature->getLastError());
            return false;
        }
        $helios->addFileFromData('fichier_pes_signe', $helios->getFileName('fichier_pes'), $signedFile);
        $this->setLastMessage("La signature a été récupérée");
        $this->notify('recu-iparapheur', $this->type, "La signature a été récupérée");
        $this->getActionCreator()->addAction($this->id_e, $this->id_u, 'recu-iparapheur', "La signature a été récupérée sur parapheur électronique");
        return true;
    }

}