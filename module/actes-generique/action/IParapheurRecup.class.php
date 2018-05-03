<?php

require_once( PASTELL_PATH . "/lib/Array2XML.class.php");

class IParapheurRecup extends ActionExecutor {

	/**
	 * @return bool
	 * @throws Exception
	 * @throws RecoverableException
	 */
	public function go(){
		/** @var SignatureConnecteur $signature */
		$signature = $this->getConnecteur('signature');

		$actes = $this->getDonneesFormulaire();
		
		$dossierID = $signature->getDossierID($actes->get('numero_de_lacte'),$actes->get('objet'));
		$erreur = false;
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


        $actes->setData('has_historique',true);
        $actes->addFileFromData('iparapheur_historique',"iparapheur_historique.xml",$historique_xml);

        $result = $signature->getLastHistorique($all_historique);
		
		if (strstr($result,"[Archive]")){
			return $this->retrieveDossier();
		} else if (strstr($result,"[RejetVisa]") || strstr($result,"[RejetSignataire]")){
            $this->rejeteDossier($dossierID,$result);
			$this->setLastMessage($result);
			return true;
		} 
		$nb_jour_max = $signature->getNbJourMaxInConnecteur();
		$lastAction = $this->getDocumentActionEntite()->getLastActionInfo($this->id_e,$this->id_d);
		$time_action = strtotime($lastAction['date']);
		if (time() - $time_action > $nb_jour_max * 86400){
			$erreur = "Aucune réponse disponible sur le parapheur depuis $nb_jour_max jours !";
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'erreur-verif-iparapheur',$erreur);		
			$this->notify('erreur-verif-iparapheur', $this->type,$erreur);
		}			
		
		if (! $erreur){
			$this->setLastMessage($result);
			return true;	
		}
		
		$this->setLastMessage($erreur);										
		return false;
					
	}

	/**
	 * @param $dossierID
	 * @param $result
	 * @return bool
	 * @throws Exception
	 */
	public function rejeteDossier($dossierID,$result){
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
        return true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 * @throws RecoverableException
	 */
	public function retrieveDossier(){
		/** @var IParapheur $signature */
		$signature = $this->getConnecteur('signature');
		
		$actes = $this->getDonneesFormulaire();
		$dossierID = $signature->getDossierID($actes->get('numero_de_lacte'),$actes->get('objet'));
		
		$info = $signature->getSignature($dossierID,false);
		if (! $info ){
			$this->setLastMessage("La signature n'a pas pu être récupérée : " . $signature->getLastError());
			return false;
		}
		
		$actes->setData('has_signature',true);
		if ($info['signature']){
			$actes->addFileFromData('signature',"signature.zip",$info['signature']);
		}		
		elseif ($info['document_signe']) {
			$actes->setData('is_pades', true);
			$actes->addFileFromData('signature',$info['document_signe']['nom_document'],$info['document_signe']['document']);
		}
		
		// Bordereau de signature
		$actes->addFileFromData('document_signe',$info['nom_document'],$info['document']);

        if (! $signature->archiver($dossierID)){
            throw new RecoverableException(
                "Impossible d'archiver la transaction sur le parapheur : " . $signature->getLastError()
                );
        }

		$this->setLastMessage("La signature a été récupérée");
		$this->notify('recu-iparapheur', $this->type,"La signature a été récupérée");
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'recu-iparapheur',"La signature a été récupérée sur parapheur électronique");			
		return true;
		
	}

	/**
	 * @param SignatureConnecteur $signature
	 * @param $message
	 * @throws Exception
	 */
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
	
}