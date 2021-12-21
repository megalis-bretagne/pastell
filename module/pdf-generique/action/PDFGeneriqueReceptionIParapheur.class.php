<?php

/** @deprecated */
class PDFGeneriqueReceptionIParapheur extends ActionExecutor
{
    public function go()
    {
        /** @var IParapheur $signature */
        $signature = $this->getConnecteur('signature');

        $donneesFormulaire = $this->getDonneesFormulaire();

        $dossierID = $donneesFormulaire->get("iparapheur_dossier_id");

        $erreur = false;
        $all_historique = array();
        try {
            $all_historique = $signature->getAllHistoriqueInfo($dossierID);
            if (! $all_historique) {
                $erreur = "La connexion avec le iParapheur a échoué : " . $signature->getLastError();
            }
        } catch (Exception $e) {
            $erreur = $e->getMessage();
        }

        if (! $erreur) {
            $array2XML = new Array2XML();
            $historique_xml = $array2XML->getXML('iparapheur_historique', json_decode(json_encode($all_historique), true));

            $donneesFormulaire->setData('has_signature', true);
            $donneesFormulaire->addFileFromData('iparapheur_historique', "iparapheur_historique.xml", $historique_xml);
            $result = $signature->getLastHistorique($all_historique);
        } else {
            $result = false;
        }

        if (strstr($result, "[Archive]")) {
            return $this->retrieveDossier();
        } elseif ($signature->isRejected($result)) {
            $this->rejeteDossier($result, $dossierID);
            $this->setLastMessage($result);
            return true;
        }
        $nb_jour_max = $signature->getNbJourMaxInConnecteur();
        $lastAction = $this->getDocumentActionEntite()->getLastActionInfo($this->id_e, $this->id_d);
        $time_action = strtotime($lastAction['date']);
        if (time() - $time_action > $nb_jour_max * 86400) {
            $erreur = "Aucune réponse disponible sur le parapheur depuis $nb_jour_max jours !";
            $this->getActionCreator()->addAction($this->id_e, $this->id_u, 'erreur-verif-iparapheur', $erreur);
            $this->notify('erreur-verif-iparapheur', $this->type, $erreur);
        }

        if (! $erreur) {
            $this->setLastMessage($result);
            return true;
        }

        $this->setLastMessage($erreur);
        return false;
    }

    public function rejeteDossier($result, $dossierID)
    {

        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');
        $donneesFormulaire = $this->getDonneesFormulaire();

        $info = $signature->getSignature($dossierID, false);
        if (! $info) {
            $this->setLastMessage("Le bordereau n'a pas pu être récupéré : " . $signature->getLastError());
            return false;
        }
        $donneesFormulaire->addFileFromData('bordereau', $info['nom_document'], $info['document']);
        $signature->effacerDossierRejete($dossierID);

        $message = "Le document a été rejeté dans le parapheur : $result";
        $this->getActionCreator()->addAction($this->id_e, $this->id_u, 'rejet-iparapheur', $message);
        $this->notify('rejet-iparapheur', $this->type, $message);
    }

    /**
     * @return bool
     * @throws Exception
     * @throws RecoverableException
     */
    public function retrieveDossier()
    {
        /** @var IParapheur $signature */
        $signature = $this->getConnecteur('signature');

        $donneesFormulaire = $this->getDonneesFormulaire();
        $dossierID = $donneesFormulaire->get("iparapheur_dossier_id");

        $info = $signature->getSignature($dossierID, false);
        if (! $info) {
            $this->setLastMessage("La signature n'a pas pu être récupérée : " . $signature->getLastError());
            return false;
        }

        $donneesFormulaire->setData('has_signature', true);
        if ($info['signature']) {
            $donneesFormulaire->addFileFromData('signature', "signature.zip", $info['signature']);
        }

        $document_original_name = $donneesFormulaire->getFileName('document');
        $document_original_data = $donneesFormulaire->getFileContent('document');
        $donneesFormulaire->addFileFromData('document_original', $document_original_name, $document_original_data);
        if ($info['document_signe']['document']) {
            $filename = substr($donneesFormulaire->getFileName('document'), 0, -4);
            $filename_signe = preg_replace("#[^a-zA-Z0-9_]#", "_", $filename) . "_signe.pdf";
            $donneesFormulaire->addFileFromData('document', $filename_signe, $info['document_signe']['document']);
        }

        $output_annexe = $signature->getOutputAnnexe($info, $donneesFormulaire->getFileNumber('annexe'));

        foreach ($output_annexe as $i => $annexe) {
            $donneesFormulaire->addFileFromData('iparapheur_annexe_sortie', $annexe['nom_document'], $annexe['document'], $i);
        }

        $donneesFormulaire->addFileFromData('bordereau', $info['nom_document'], $info['document']);

        if (! $signature->archiver($dossierID)) {
            throw new RecoverableException(
                "Impossible d'archiver la transaction sur le parapheur : " . $signature->getLastError()
            );
        }

        $this->setLastMessage("La signature a été récupérée");
        $this->notify('recu-iparapheur', $this->type, "La signature a été récupérée");
        $this->getActionCreator()->addAction($this->id_e, $this->id_u, 'recu-iparapheur', "La signature a été récupérée sur le parapheur électronique");
        return true;
    }
}
