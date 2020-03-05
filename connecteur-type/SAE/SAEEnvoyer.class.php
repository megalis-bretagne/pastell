<?php

class SAEEnvoyer extends ConnecteurTypeActionExecutor
{


    public const ACTION_NAME = 'send-archive';
    public const ACTION_NAME_ERROR = 'erreur-envoie-sae';

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {

        /** @var TmpFolder $tmpFolder */
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        $result = false;

        try {
            $result = $this->goThrow($tmp_folder);
        } catch (UnrecoverableException $e) {
            $this->changeAction(self::ACTION_NAME_ERROR, $e->getMessage());
            $this->notify(self::ACTION_NAME_ERROR, $this->type, $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        } finally {
            $tmpFolder->delete($tmp_folder);
        }

        return $result;
    }

    /**
     * @param $tmp_folder
     * @return bool
     * @throws Exception
     * @throws UnrecoverableException
     */
    public function goThrow($tmp_folder)
    {

        $sae_show = $this->getMappingValue('sae_show');
        $sae_bordereau = $this->getMappingValue('sae_bordereau');
        $sae_archive = $this->getMappingValue('sae_archive');
        $sae_transfert_id = $this->getMappingValue('sae_transfert_id');
        $sae_config = $this->getMappingValue('sae_config');

        $donneesFormulaire = $this->getDonneesFormulaire();
        $donneesFormulaire->setData($sae_show, true);
        $this->createJournal();

        /** @var SEDAConnecteur $sedaNG */
        $sedaNG = $this->getConnecteur('Bordereau SEDA');

        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');


        $fluxDataClassName = $this->getDataSedaClassName();
        $fluxDataClassPath = $this->getDataSedaClassPath();

        if (! $fluxDataClassPath) {
            $fluxDataClassPath = __DIR__ . "/../../connecteur/seda-ng/lib/FluxDataSedaDefault.class.php";
            $fluxDataClassName = 'FluxDataSedaDefault';
        }

        require_once $fluxDataClassPath;
        /** @var FluxData $fluxData */
        $fluxData = new $fluxDataClassName(
            $donneesFormulaire
        );

        $metadata = json_decode($donneesFormulaire->getFileContent($sae_config), true) ?: array();
        if (method_exists($fluxData, "setMetadata")) {
            $fluxData->setMetadata($metadata);
        }

        $bordereau = $sedaNG->getBordereauNG($fluxData);
        $donneesFormulaire->addFileFromData($sae_bordereau, "bordereau.xml", $bordereau);
        $transferId = $sae->getTransferId($bordereau);
        $donneesFormulaire->setData($sae_transfert_id, $transferId);

        try {
            $sedaNG->validateBordereau($bordereau);
        } catch (Exception $e) {
            $message = $e->getMessage() . " : <br/><br/>";
            foreach ($sedaNG->getLastValidationError() as $erreur) {
                $message .= $erreur->message . "<br/>";
            }
            throw new UnrecoverableException($message);
        }

        $archive_path = $tmp_folder . "/archive.tar.gz";
        // ! generateArchive doit être postérieur à getBordereauNG afin que la liste des fichiers à traiter (file_list de FluxDataSedaDefault) soit renseignée.
        $sedaNG->generateArchive($fluxData, $archive_path);

        $donneesFormulaire->addFileFromCopy($sae_archive, "archive.tar.gz", $archive_path);

        $result = $sae->sendArchive($bordereau, $archive_path);

        if (! $result) {
            $this->setLastMessage("L'envoi du bordereau a échoué : " . $sae->getLastError());
            return false;
        }

        $this->addActionOK("Le document a été envoyé au SAE");
        $this->notify($this->action, $this->type, "Le document a été envoyé au SAE");
        return true;
    }

    /**
     * @throws Exception
     */
    private function createJournal()
    {

        $journal_mapping = $this->getMappingValue('journal');
        $date_journal_debut_mapping = $this->getMappingValue('date_journal_debut');
        $date_cloture_journal_mapping = $this->getMappingValue('date_cloture_journal');
        $date_cloture_journal_iso8601_mapping = $this->getMappingValue('date_cloture_journal_iso8601');


        $journal = $this->getJournal()->getAll($this->id_e, false, $this->id_d, 0, 0, 10000);
        foreach ($journal as $i => $journal_item) {
            $journal[$i]['preuve'] = base64_encode($journal[$i]['preuve']);
        }

        $date_journal_debut = $journal[count($journal) - 1]['date'];
        $date_cloture_journal = $journal[0]['date'];

        $journal = json_encode($journal);

        $this->getDonneesFormulaire()->addFileFromData($journal_mapping, 'journal.json', $journal);
        $this->getDonneesFormulaire()->setData(
            $date_journal_debut_mapping,
            date("Y-m-d", strtotime($date_journal_debut))
        );
        $this->getDonneesFormulaire()->setData(
            $date_cloture_journal_mapping,
            date("Y-m-d", strtotime($date_cloture_journal))
        );
        $this->getDonneesFormulaire()->setData(
            $date_cloture_journal_iso8601_mapping,
            date('c', strtotime($date_cloture_journal))
        );
    }
}
