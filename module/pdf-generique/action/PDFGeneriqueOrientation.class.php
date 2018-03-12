<?php

class PDFGeneriqueOrientation extends ActionExecutor {

    public function go(){
        $last_action = $this->getDocumentActionEntite()->getLastAction($this->id_e,$this->id_d);

        if (! $last_action){
            throw new Exception("Erreur : la dernière action de ce document n'a pas été récupéré");
        }

        $next_action = $this->getNextAction($last_action);

        $message = "Changement d'état : {$last_action} -> {$next_action}";
        $this->getActionCreator()->addAction($this->id_e,$this->id_u,$next_action,"$message");

        $this->notify($next_action,$this->type,$message);
        $this->setLastMessage($message);
        return true;
    }

    private function getNextAction($action){

        if (($action == 'modification') || ($action == 'importation')){
            if ($this->getDonneesFormulaire()->get('envoi_signature')){
                return 'preparation-send-iparapheur';
            }
            if ($this->getDonneesFormulaire()->get('envoi_ged_1')){
                return 'preparation-send-ged-1';
            }
            if ($this->getDonneesFormulaire()->get('envoi_mailsec')){
                return 'preparation-send-mailsec';
            }
            if ($this->getDonneesFormulaire()->get('envoi_ged_2')){
                return 'preparation-send-ged-2';
            }
            if ($this->getDonneesFormulaire()->get('envoi_sae')){
                return 'preparation-send-sae';
            }
            return "preparation-suppression-automatique";
        }
        if ($action == 'recu-iparapheur'){
            if ($this->getDonneesFormulaire()->get('envoi_ged_1')){
                return 'preparation-send-ged-1';
            }
            if ($this->getDonneesFormulaire()->get('envoi_mailsec')){
                return 'preparation-send-mailsec';
            }
            if ($this->getDonneesFormulaire()->get('envoi_ged_2')){
                return 'preparation-send-ged-2';
            }
            if ($this->getDonneesFormulaire()->get('envoi_sae')){
                return 'preparation-send-sae';
            }
            return "preparation-suppression-automatique";

        }
        if ($action == 'send-ged-1'){
            if ($this->getDonneesFormulaire()->get('envoi_mailsec')){
                return 'preparation-send-mailsec';
            }
            if ($this->getDonneesFormulaire()->get('envoi_ged_2')){
                return 'preparation-send-ged-2';
            }
            if ($this->getDonneesFormulaire()->get('envoi_sae')){
                return 'preparation-send-sae';
            }
            return "preparation-suppression-automatique";
        }
        if (in_array($action,array('reception','non-recu','erreur'))){
            if ($action == 'reception') {
                $this->getDonneesFormulaire()->setData('is_recupere','1');
            }
            if ($this->getDonneesFormulaire()->get('envoi_ged_2')){
                return 'preparation-send-ged-2';
            }
            if ($this->getDonneesFormulaire()->get('envoi_sae')){
                return 'preparation-send-sae';
            }
            return "preparation-suppression-automatique";
        }
        if ($action == 'send-ged-2'){
            if ($this->getDonneesFormulaire()->get('envoi_sae')){
                return 'preparation-send-sae';
            }
            return "preparation-suppression-automatique";
        }
        if ($action == 'accepter-sae'){
            return "preparation-suppression-automatique";
        }
        throw new Exception("L'action suivante de $action n'est pas défini. Arret du flux");
    }


}