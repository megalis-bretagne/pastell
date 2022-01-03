<?php

require_once PASTELL_PATH . '/connecteur-type/TdT/lib/PESAcquitFile.class.php';

/** @deprecated PA 3.0.0 utiliser TdtRecupHelios à la place */
class TedetisRecupHelios extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur('TdT');

        $tedetis_transaction_id = $this->getDonneesFormulaire()->get('tedetis_transaction_id');

        $actionCreator = $this->getActionCreator();
        if (!$tedetis_transaction_id) {
            $message = "Une erreur est survenu lors de l'envoi à " . $tdT->getLogicielName();
            $this->setLastMessage($message);
            $actionCreator->addAction($this->id_e, 0, 'tdt-error', $message);
            $this->notify('tdt-error', $this->type, $message);
            return false;
        }

        $status = $tdT->getStatusHelios($tedetis_transaction_id);

        if ($status === false) {
            $this->setLastMessage($tdT->getLastError());
            return false;
        }

        $status_info = $tdT->getStatusInfo($status);

        if ($status == TdtConnecteur::STATUS_ERREUR) {
            $message = 'Transaction en erreur sur le TdT';
            if ($tdT->getLastReponseFile()) {
                $xml = simplexml_load_string($tdT->getLastReponseFile());
                if ($xml) {
                    $message = utf8_decode($xml->{'message'});
                }
            }
            $this->setLastMessage($message);
            $this->getActionCreator()->addAction($this->id_e, $this->id_u, 'tdt-error', $message);
            $this->notify('tdt-error', $this->type, $message);
            return false;
        }

        $next_message = "La transaction est dans l'état : $status_info ($status) ";
        $next_action = "";
        if ($status == TdtConnecteur::STATUS_ACQUITTEMENT_RECU) {
            $next_action = 'acquiter-tdt';
            $next_message = 'Un acquittement PES a été recu';
        }
        if ($status == TdtConnecteur::STATUS_REFUSE) {
            $next_action = 'refus-tdt';
            $next_message = 'Le fichier PES a été refusé';
        }
        if ($status == TdtConnecteur::STATUS_HELIOS_INFO) {
            $next_action = 'info-tdt';
            $next_message = 'Une réponse est disponible pour ce fichier PES';
        }
        if (in_array($status, array(TdtConnecteur::STATUS_ACQUITTEMENT_RECU, TdtConnecteur::STATUS_REFUSE, TdtConnecteur::STATUS_HELIOS_INFO))) {
            $this->getDonneesFormulaire()->setData('has_reponse', true);
            $retour = $tdT->getFichierRetour($tedetis_transaction_id);
            $this->getDonneesFormulaire()->addFileFromData('fichier_reponse', 'retour.xml', $retour);
            $actionCreator->addAction($this->id_e, 0, $next_action, $next_message);
            $this->notify('acquiter-tdt', $this->type, $next_message);
            $this->recupPESAcquitInfo();
        }
        $this->setLastMessage($next_message);
        return true;
    }

    /**
     * @throws Exception
     */
    public function recupPESAcquitInfo()
    {
        $heliosGeneriquePESAcquit = new PESAcquitFile();
        $etat_ack = $heliosGeneriquePESAcquit->getEtatAck($this->getDonneesFormulaire()->getFilePath('fichier_reponse')) ? 1 : 2;
        $this->getDonneesFormulaire()->setData('etat_ack', $etat_ack);
    }
}
