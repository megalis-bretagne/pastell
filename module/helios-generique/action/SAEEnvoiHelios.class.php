<?php

class SAEEnvoiHelios extends ActionExecutor
{
    public function go()
    {
        /** @var TmpFolder $tmpFolder */
        $tmpFolder = $this->objectInstancier->getInstance(TmpFolder::class);
        $tmp_folder = $tmpFolder->create();

        $result = false;
        try {
            $result = $this->goThrow($tmp_folder);
        } catch (Exception $e) {
            throw $e;
        } finally {
            $tmpFolder->delete($tmp_folder);
        }
        return $result;
    }

    public function goThrow($tmp_folder)
    {
        $donneesFormulaire = $this->getDonneesFormulaire();
        if (! $donneesFormulaire->get('envoi_signature') && ! $donneesFormulaire->get('fichier_pes_signe')) {
            $fichier_pes = $donneesFormulaire->getFileContent('fichier_pes');
            $file_name = $donneesFormulaire->get('fichier_pes');
            $donneesFormulaire->addFileFromData('fichier_pes_signe', $file_name[0] ?? '', $fichier_pes);
        }

        $pes_aller = $donneesFormulaire->copyFile('fichier_pes_signe', $tmp_folder, 0, "pes_aller");
        $pes_retour = $donneesFormulaire->copyFile('fichier_reponse', $tmp_folder, 0, "pes_retour");

        if ($donneesFormulaire->get('iparapheur_historique')) {
            $iparapheur_historique = $donneesFormulaire->copyFile('iparapheur_historique', $tmp_folder, 0, "iparapheur_historique");
        } else {
            $iparapheur_historique = false;
        }

        $transactionsInfo = array(
                'unique_id' => $donneesFormulaire->get('tedetis_transaction_id'),
                'date' => date("Y-m-d"),
                'description' => 'inconnu',
                'pes_retour_description' => 'inconnu',
                'pes_aller' => $pes_aller,
                'pes_retour' => $pes_retour,
                'pes_aller_original_filename' => $donneesFormulaire->getFileName('fichier_pes_signe', 0),
                'pes_retour_original_filename' => $donneesFormulaire->getFileName('fichier_reponse', 0),
                'pes_description' => 'inconnu',
                'pes_aller_content' => $donneesFormulaire->getFileContent('fichier_pes_signe'),
                'iparapheur_historique' => $iparapheur_historique
        );

        /** @var SEDAConnecteur $heliosSEDA */
        $heliosSEDA = $this->getConnecteur('Bordereau SEDA');

        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');


        if ($heliosSEDA instanceof SedaNG) {
            /** @var SedaNG $heliosSEDA */
            $fluxData = new FluxDataSedaHelios($donneesFormulaire);
            $bordereau = $heliosSEDA->getBordereauNG($fluxData);
            $donneesFormulaire->addFileFromData('sae_bordereau', "bordereau.xml", $bordereau);
            $transferId = $sae->getTransferId($bordereau);
            $donneesFormulaire->setData("sae_transfert_id", $transferId);

            try {
                $heliosSEDA->validateBordereau($bordereau);
            } catch (Exception $e) {
                $message = $e->getMessage() . " : <br/><br/>";
                foreach ($heliosSEDA->getLastValidationError() as $erreur) {
                    $message .= $erreur->message . "<br/>";
                }
                throw new Exception($message);
            }

            $archive_path = $tmp_folder . "/archive.tar.gz";
            // ! generateArchive doit être postérieur à getBordereauNG afin que la liste des fichiers à traiter (file_list de FluxDataSedaDefault) soit renseignée.
            $heliosSEDA->generateArchive($fluxData, $archive_path);
        } else {
            $bordereau = $heliosSEDA->getBordereau($transactionsInfo);
            $archive_path = $sae->generateArchive($bordereau, $tmp_folder);
        }


        $transferId = $sae->getTransferId($bordereau);
        $donneesFormulaire->setData("sae_transfert_id", $transferId);
        $donneesFormulaire->addFileFromData('sae_bordereau', "bordereau.xml", $bordereau);
        $donneesFormulaire->addFileFromCopy('sae_archive', "archive.tar.gz", $archive_path);

        $result = $sae->sendArchive($bordereau, $archive_path);

        if (! $result) {
            $this->setLastMessage("L'envoi du bordereau a échoué : " . $sae->getLastError());
            return false;
        }

        $this->addActionOK("Le document a été envoyé au SAE");
        $this->notify($this->action, $this->type, "Le document a été envoyé au SAE");
        return true;
    }
}
