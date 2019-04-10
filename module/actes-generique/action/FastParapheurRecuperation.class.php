<?php

require_once PASTELL_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Array2XML.class.php';

class FastParapheurRecuperation extends ActionExecutor
{

    /**
     * @throws Exception
     */
    public function go()
    {
        /** @var FastParapheur $signature */
        $signature = $this->getConnecteur('signature');

        $acte = $this->getDonneesFormulaire();

        $documentId = $acte->get('iparapheur_dossier_id');

        $history = $this->getFileHistory($signature, $documentId);

        $error = false;

        $array2XML = new Array2XML();
        $xmlHistory = $array2XML->getXML('iparapheur_historique', json_decode(json_encode($history), true));
        $acte->setData('has_historique', true);
        $acte->addFileFromData('iparapheur_historique', "history.xml", $xmlHistory);

        $lastDocumentHistory = $signature->getLastHistorique($history);
        if (strstr($lastDocumentHistory, "[Classé]")) {
            $this->retrieveFile($signature, $acte, $documentId);
        }

        $nb_jour_max = $signature->getNbJourMaxInConnecteur();
        $lastAction = $this->getDocumentActionEntite()->getLastActionInfo($this->id_e, $this->id_d);
        $time_action = strtotime($lastAction['date']);
        if (time() - $time_action > $nb_jour_max * 86400) {
            $error = "Aucune réponse disponible sur le parapheur depuis $nb_jour_max jours !";
            $this->getActionCreator()->addAction($this->id_e, $this->id_u, 'erreur-verif-signature-fast', $error);
            $this->notify('erreur-verif-signature-fast', $this->type, $error);
        }

        if (!$error) {
            $this->setLastMessage($lastDocumentHistory);
            return true;
        }

        $this->setLastMessage($error);
        return false;
    }

    /**
     * @param FastParapheur $signature
     * @param DonneesFormulaire $acte
     * @param $documentId
     * @return bool
     * @throws Exception
     */
    private function retrieveFile(FastParapheur $signature, DonneesFormulaire $acte, $documentId)
    {
        $signedFile = $signature->getSignature($documentId);
        if (!$signedFile) {
            $this->setLastMessage("La signature n'a pas pu être récupérée : " . $signature->getLastError());
            return false;
        }
        $acte->addFileFromData('signature', $acte->getFileName('arrete'), $signedFile);
        $this->setLastMessage("La signature a été récupérée");
        $this->notify('recu-iparapheur', $this->type, "La signature a été récupérée");
        $this->getActionCreator()->addAction($this->id_e, $this->id_u, 'recu-iparapheur', "La signature a été récupérée sur parapheur électronique");
        return true;
    }


    /**
     * @param FastParapheur $signature
     * @param $documentId
     * @return bool
     * @throws Exception
     */
    private function getFileHistory(FastParapheur $signature, $documentId)
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
     * @param SignatureConnecteur $signature
     * @param $message
     * @throws Exception
     */
    public function throwError(SignatureConnecteur $signature, $message)
    {
        $nb_jour_max = $signature->getNbJourMaxInConnecteur();
        $lastAction = $this->getDocumentActionEntite()->getLastActionInfo($this->id_e, $this->id_d);
        $time_action = strtotime($lastAction['date']);

        if (time() - $time_action > $nb_jour_max * 86400) {
            $message = "Aucune réponse disponible sur le parapheur depuis $nb_jour_max jours !";
            $this->getActionCreator()->addAction($this->id_e, $this->id_u, 'erreur-verif-signature-fast', $message);
            $this->notify($this->action, $this->type, $message);
        }

        throw new Exception($message);
    }
}

