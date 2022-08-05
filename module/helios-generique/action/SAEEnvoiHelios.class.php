<?php

class SAEEnvoiHelios extends ActionExecutor
{
    /**
     * @throws UnrecoverableException
     * @throws NotFoundException
     * @throws DonneesFormulaireException
     * @throws Exception
     */
    public function go()
    {
        $tmpFolder = $this->objectInstancier->getInstance(TmpFolder::class);
        $tmp_folder = $tmpFolder->create();

        try {
            $result = $this->goThrow($tmp_folder);
        } finally {
            $tmpFolder->delete($tmp_folder);
        }
        return $result;
    }

    /**
     * @throws UnrecoverableException
     * @throws NotFoundException
     * @throws DonneesFormulaireException
     * @throws Exception
     */
    public function goThrow(string $tmp_folder): bool
    {
        $donneesFormulaire = $this->getDonneesFormulaire();
        if (! $donneesFormulaire->get('envoi_signature') && ! $donneesFormulaire->get('fichier_pes_signe')) {
            $fichier_pes = $donneesFormulaire->getFileContent('fichier_pes');
            $file_name = $donneesFormulaire->get('fichier_pes');
            $donneesFormulaire->addFileFromData('fichier_pes_signe', $file_name[0] ?? '', $fichier_pes);
        }

        /** @var SEDAConnecteur $heliosSEDA */
        $heliosSEDA = $this->getConnecteur('Bordereau SEDA');

        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');

        $fluxData = new FluxDataSedaHelios($donneesFormulaire);
        $bordereau = $heliosSEDA->getBordereau($fluxData);

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

        $donneesFormulaire->addFileFromData('sae_bordereau', "bordereau.xml", $bordereau);
        $donneesFormulaire->addFileFromCopy('sae_archive', "archive.tar.gz", $archive_path);

        $result = $sae->sendArchive($bordereau, $archive_path);

        if (! $result) {
            $this->setLastMessage("L'envoi du bordereau a échoué : " . $sae->getLastError());
            return false;
        }
        $donneesFormulaire->setData('sae_transfert_id', $result);

        $this->addActionOK("Le document a été envoyé au SAE");
        $this->notify($this->action, $this->type, "Le document a été envoyé au SAE");
        return true;
    }
}
