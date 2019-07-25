<?php

class TdtVerifReponsePref extends ConnecteurTypeActionExecutor {

    private $many_same_message;

    public function go(){

        $acte_transaction_id = $this->getMappingValue('acte_transaction_id');
        $reponse_transaction_id = $this->getMappingValue('reponse_transaction_id');
        $type_reponse = $this->getMappingValue('reponse_transaction_id');

        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur("TdT");

        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur("TdT");

        if (!$tdT){
            throw new UnrecoverableException("Aucun Tdt disponible");
        }

        $acte_transaction_id = $this->getDonneesFormulaire()->get($acte_transaction_id);
        $reponse_transaction_id = $this->getDonneesFormulaire()->get($reponse_transaction_id);

        if (( ! $acte_transaction_id) || ( ! $reponse_transaction_id)){
            $message="L'identifiant de la transaction est manquant pour pouvoir vérifier l'acquitement";
            $this->setLastMessage($message);
            return false;
        }

        if (($type_reponse != (TdtConnecteur::DEMANDE_PIECE_COMPLEMENTAIRE) || ($type_reponse != TdtConnecteur::LETTRE_OBSERVATION))){
            $message="Ce type de réponse de la préfécture ne prévoit pas d'acquittement";
            $this->setLastMessage($message);
            return false;
        }

        // TODO

/*
        $all_response = $tdT->getListReponsePrefecture($acte_transaction_id);

        if (!$all_response)  {
            $this->setLastMessage("Aucune réponse disponible");
            return true;
        }
        foreach($all_response as $response){
            $this->saveAutreDocument($response);
        }

        $last_action = $this->getDocumentActionEntite()->getLastActionNotModif($this->id_e,$this->id_d);
        $this->verifReponseAttendu($last_action);

        if ($this->many_same_message){
            $this->setLastMessage("Attention, il y a plusieurs messages de même type, cette situation n'est pas traitée par Pastell : ".implode(",",$this->many_same_message));
            return false;
        }

        $this->setLastMessage("Réponses récupérées");
        return true;
*/
    }

    private function verifReponseAttendu($last_action){
        if ($last_action == 'attente-reponse-prefecture' || $last_action == 'envoie-reponse-prefecture'){
            return;
        }
        foreach(array(2,3,4) as $id_type) {
            $libelle = $this->getLibelleType($id_type);
            if($this->getDonneesFormulaire()->get("has_$libelle") == true){
                if ($this->getDonneesFormulaire()->get("has_reponse_$libelle") == false){
                    $this->getActionCreator()->addAction($this->id_e,$this->id_u,'attente-reponse-prefecture',"Attente d'une réponse");
                    return;
                }
            }
        }

    }


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
        $tdT = $this->getConnecteur("TdT");

        $type = $this->getLibelleType($response['type']);
        $has_acquittement = $this->getDonneesFormulaire()->get("{$type}_has_acquittement");
        if ($has_acquittement){
            return false;
        }
        $this->getDonneesFormulaire()->setData("{$type}_has_acquittement",true);

    }

    private function saveReponse($response){
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