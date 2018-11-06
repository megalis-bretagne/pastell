<?php

class SAEVerifier extends ConnecteurTypeActionExecutor {


    const ACTION_NAME_RECU = 'ar-recu-sae';
    const ACTION_NAME_ERROR = 'verif-sae-erreur';

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
        $ar_sae = $this->getMappingValue('ar_sae');

        $id_transfert = $donneesFormulaire->get($sae_transfert_id);
        $ar = $sae->getAcuseReception($id_transfert);

        if (! $ar){
            if ($sae->getLastErrorCode() == 7){
                $max_delai_ar = $sae_config->get("max_delai_ar") * 60;
                $lastAction = $this->getDocumentActionEntite()->getLastAction($this->id_e,$this->id_d);
                $time_action = strtotime($lastAction['date']);
                if (time() - $time_action < $max_delai_ar){
                    $this->setLastMessage("L'accusé de réception n'est pas encore disponible");
                    return false;
                }
            }

            $message = $sae->getLastError();
            $this->setLastMessage($message);
            $this->getActionCreator()->addAction($this->id_e,$this->id_u,self::ACTION_NAME_ERROR,$message);
            $this->notify($this->action, $this->type,$message);
            return false;
        }

        $donneesFormulaire->addFileFromData($ar_sae,'ar.xml',$ar);

        $xml = simplexml_load_string($ar);
        $message = strval($xml->ReplyCode) . " - " . strval($xml->Comment);

        $message = "Récupération de l'accusé de réception : $message";
        $this->getActionCreator()->addAction($this->id_e,$this->id_u,self::ACTION_NAME_RECU,$message);

        $this->notify(self::ACTION_NAME_RECU, $this->type,$message);
        $this->setLastMessage($message);
        return true;
    }


}