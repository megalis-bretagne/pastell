<?php

class TdtVerifReponsePref extends ConnecteurTypeActionExecutor {

    private $many_same_message;

    /**
     * @return bool
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function go(){

        $acte_transaction_id = $this->getMappingValue('acte_transaction_id');
        $reponse_transaction_id = $this->getMappingValue('reponse_transaction_id');
        $type_reponse = $this->getMappingValue('type_reponse');

        $tdt_error = $this->getMappingValue('tdt-error');
        $erreur_verif_tdt = $this->getMappingValue('erreur-verif-tdt');
        $termine = $this->getMappingValue('termine');

        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur("TdT");

        if (!$tdT){
            throw new UnrecoverableException("Aucun Tdt disponible");
        }

        $acte_transaction_id_element = $this->getDonneesFormulaire()->get($acte_transaction_id);
        $reponse_transaction_id_element = $this->getDonneesFormulaire()->get($reponse_transaction_id);
        $type_reponse_element = $this->getDonneesFormulaire()->get($type_reponse);
        $reponse_de_reponse_transaction_id = $this->getLibelleType($type_reponse_element).'_response_transaction_id';
        $reponse_de_reponse_transaction_id_element = $this->getDonneesFormulaire()->get($reponse_de_reponse_transaction_id);

        $actionCreator = $this->getActionCreator();

        if (( ! $acte_transaction_id_element) || ( ! $reponse_transaction_id_element)){
            $message="Une erreur est survenue lors de l'envoi à ".$tdT->getLogicielName()." (tedetis_transaction_id non disponible)";
            $this->setLastMessage($message);
            $actionCreator->addAction($this->id_e,0,$tdt_error,$message);
            $this->notify($tdt_error, $this->type,$message);
            return false;
        }

        if (!in_array($type_reponse_element, [TdtConnecteur::DEMANDE_PIECE_COMPLEMENTAIRE, TdtConnecteur::LETTRE_OBSERVATION])) {
            $message="Ce type de réponse de la préfécture ne prévoit pas d'acquittement";
            $actionCreator->addAction($this->id_e,0,$termine,$message);
            $this->setLastMessage($message);
            return false;
        }

        try {
            $status = $tdT->getStatus($reponse_de_reponse_transaction_id_element);
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

        // TODO
        // Voir TdTRecupActe.class.php

    }

    private function getLibelleType($id_type)
    {
        $txt_message = [
            TdTConnecteur::COURRIER_SIMPLE => 'courrier_simple',
            TdtConnecteur::DEMANDE_PIECE_COMPLEMENTAIRE => 'demande_piece_complementaire',
            TdtConnecteur::LETTRE_OBSERVATION => 'lettre_observation',
            TdtConnecteur::DEFERE_TRIBUNAL_ADMINISTRATIF => 'defere_tribunal_administratif',
            6 => 'annulation'
        ];

        return $txt_message[$id_type];
    }

    /**
     * @param $response
     * @return bool
     * @throws Exception
     */
    private function saveAutreDocument($response){
        if ($response['status'] == TdtConnecteur::STATUS_ACTES_MESSAGE_PREF_RECU
            || $response['status'] == TdtConnecteur::STATUS_ACTES_MESSAGE_PREF_RECU_AR
            || $response['status'] == TdtConnecteur::STATUS_ACTES_MESSAGE_PREF_RECU_PAS_D_AR
        ) {
            return $this->saveReponse($response);
        }
        if ($response['status'] == TdtConnecteur::STATUS_ACTES_MESSAGE_PREF_ACQUITTEMENT_RECU
            || $response['status'] == TdtConnecteur::STATUS_ACQUITTEMENT_RECU
        ) {
            return $this->saveAcquittement($response);
        }
    }

    private function saveAcquittement($response){
        $type = $this->getLibelleType($response['type']);
        $has_acquittement = $this->getDonneesFormulaire()->get("{$type}_has_acquittement");
        if ($has_acquittement){
            return false;
        }
        $this->getDonneesFormulaire()->setData("{$type}_has_acquittement",true);

        /*
        $message = "Réception d'un message ($type) de la préfecture";
        $this->addActionOK($message);
        $this->notify('verif-reponse-tdt', $this->type, $message);
        return true;
         */
    }

    /**
     * @param $response
     * @return bool
     * @throws Exception
     */
    private function saveReponse($response){
        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur("TdT");

        $type = $this->getLibelleType($response['type']);
        $type_id = $this->getDonneesFormulaire()->get("{$type}_id");
        if ($type_id){
            if ($type_id != $response['id']){
                $this->many_same_message[] = $type;
            }
            return false;
        }

        $file_content = $tdT->getReponsePrefecture($response['id']);
        $this->getDonneesFormulaire()->setData("has_{$type}",true);
        $this->getDonneesFormulaire()->setData("{$type}_id",$response['id']);
        $this->getDonneesFormulaire()->setData("{$type}_date",date("Y-m-d H:i:m"));
        $this->getDonneesFormulaire()->addFileFromData("{$type}","{$type}.tar.gz", $file_content);

        $this->objectInstancier->DonneesFormulaireTarBall->extract($this->getDonneesFormulaire(), "{$type}", "{$type}_unzip");

        $message = "Réception d'un message ($type) de la préfecture";
        $this->addActionOK($message);
        $this->notify('verif-reponse-tdt', $this->type, $message);
        return true;
    }


}