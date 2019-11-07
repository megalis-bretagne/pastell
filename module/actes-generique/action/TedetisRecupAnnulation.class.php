<?php

class TedetisRecupAnnulation extends ActionExecutor
{

    public function go()
    {
        $tdT = $this->getConnecteur("TdT");
        
        if (!$tdT) {
            throw new Exception("Aucun Tdt disponible");
        }
        
        $tedetis_transaction_id = $this->getDonneesFormulaire()->get('tedetis_annulation_id');
        
        $actionCreator = $this->getActionCreator();
        if (! $tedetis_transaction_id) {
            $message = "Une erreur est survenue lors de l'envoi à " . $tdT->getLogicielName();
            $this->setLastMessage($message);
            $actionCreator->addAction($this->id_e, 0, 'tdt-error', $message);
            $this->notify('tdt-error', $this->type, $message);
            return false;
        }
            
        try {
            $status = $tdT->getStatus($tedetis_transaction_id);
        } catch (Exception $e) {
            $message = "Echec de la récupération des informations : " .  $e->getMessage();
            $this->setLastMessage($message);
            return false;
        }
        if ($status != TdtConnecteur::STATUS_ACQUITTEMENT_RECU) {
            $this->setLastMessage("La transaction d'annulation a comme statut : " . TdtConnecteur::getStatusString($status));
            return true;
        }
        $actionCreator->addAction($this->id_e, 0, 'annuler-tdt', "L'acte a été annulé par le contrôle de légalité");
        
        $this->getDonneesFormulaire()->setData('date_ar_annulation', $tdT->getDateAR($tedetis_transaction_id));
        
        $message = "L'acquittement pour l'annulation de l'acte a été reçu.";
        $this->notify('annuler-tdt', $this->type, $message);
        $this->setLastMessage($message);
        return true;
    }
}
