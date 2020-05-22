<?php

class TdtRetourTeletransmettre extends ActionExecutor
{

    public function go()
    {

        $stringMapper = $this->getDocumentType()->getAction()->getConnecteurMapper($this->action);

        $recuperateur = new Recuperateur($_GET);
        $error = $recuperateur->get("error");
        $message = $recuperateur->get("message");
        if ($error) {
            throw new Exception("Erreur sur le Tdt : " . $message);
        }
        
        $tdt = $this->getConnecteur("TdT");
        
        $tedetis_transaction_id = $this->getDonneesFormulaire()->get($stringMapper->get('tedetis_transaction_id'));
        
        $status =  $tdt->getStatus($tedetis_transaction_id);
        
        //A priori, c'est le seul cas que je vois ou la transaction n'a pas encore été posté
        if (in_array($status, array(TdtConnecteur::STATUS_ACTES_EN_ATTENTE_DE_POSTER))) {
            throw new Exception("La transaction n'a pas le bon statut : " . TdtConnecteur::getStatusString($status) . " trouvé") ;
        }

        $this->addActionOK("Le document a été télétransmis à la préfecture");
        $this->notify($this->action, $this->type, "Le document a été télétransmis à la préfecture");
        
        return true;
    }
}
