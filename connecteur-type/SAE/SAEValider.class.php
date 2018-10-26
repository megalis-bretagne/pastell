<?php

class SAEValider extends ConnecteurTypeActionExecutor {


    const ACTION_NAME_ERROR_ENVOI = 'erreur-envoie-sae';
    const ACTION_NAME_ERROR_VALIDATION = 'validation-sae-erreur';
    const ACTION_NAME_ACCEPTER = 'accepter-sae';
    const ACTION_NAME_REJET = 'rejet-sae';

    /**
     * @return bool
     * @throws Exception
     */
    public function go(){
        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');
        $sae_config = $this->getConnecteurConfigByType('SAE');

        $donneesFormulaire = $this->getDonneesFormulaire();

        $sae_transfert_id = $this->getMappingValue('sae_transfert_id');
        $reply_sae = $this->getMappingValue('reply_sae');

        $id_transfert = $donneesFormulaire->get($sae_transfert_id);

        if (!$id_transfert){
            $message = "Impossible de trouver l'identifiant du transfert";
            $this->setLastMessage($message);
            $this->getActionCreator()->addAction($this->id_e,$this->id_u,self::ACTION_NAME_ERROR_ENVOI,$message);
            $this->notify($this->action, $this->type,$message);
            return false;
        }
        $validation = $sae->getReply($id_transfert);

        if (! $validation){
            if ($sae->getLastErrorCode() == 8){
                $max_delai_ar = $sae_config->get("max_delai_validation") * 24 * 60 * 60;
                $lastAction = $this->getDocumentActionEntite()->getLastAction($this->id_e,$this->id_d);
                $time_action = strtotime($lastAction['date']);
                if (time() - $time_action < $max_delai_ar){
                    $this->setLastMessage("Le document n'a pas encore été traité");
                    return false;
                }
            }

            $message = $sae->getLastError();
            $this->setLastMessage($message);
            $this->getActionCreator()->addAction($this->id_e,$this->id_u,self::ACTION_NAME_ERROR_VALIDATION,$message);
            $this->notify($this->action, $this->type,$message);
            return false;
        }

        $donneesFormulaire->addFileFromData($reply_sae,'reply.xml',$validation);

        $xml = simplexml_load_string($validation);

        if (! $xml){
            throw new Exception("Impossible de lire le contenu de la réponse du SAE");
        }

        $nodeName = strval($xml->getName());
        if ($nodeName == 'ArchiveTransferAcceptance' || ($nodeName == 'ArchiveTransferReply' && (strval($xml->ReplyCode) == '000'))){
            $url = $sae->getURL(strval($xml->Archive->ArchivalAgencyArchiveIdentifier));
            $donneesFormulaire->setData('url_archive', $url);
            $message = "La transaction a été acceptée par le SAE";
            $next_action = self::ACTION_NAME_ACCEPTER;

        } else {
            $message = "La transaction a été refusée par le SAE";
            $next_action = self::ACTION_NAME_REJET;
        }

        $this->getActionCreator()->addAction($this->id_e,$this->id_u,$next_action,$message);
        $this->notify($next_action, $this->type,$message);

        $this->setLastMessage($message);
        return true;
    }
}