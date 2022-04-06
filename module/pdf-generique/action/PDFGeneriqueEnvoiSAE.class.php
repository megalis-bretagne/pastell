<?php

class PDFGeneriqueEnvoiSAE extends ActionExecutor
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

        $this->getDonneesFormulaire()->setData("sae_show", true);

        $this->createJournal();

        /** @var SEDANG $sedaNG */

        $sedaNG = $this->getConnecteur('Bordereau SEDA');
        if (! $sedaNG) {
            throw new Exception("Connnecteur SEDA NG non trouvé");
        }

        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');

        $fluxData = new FluxDataSedaPDFGenerique(
            $this->getDonneesFormulaire()
        );

        $metadata = json_decode($this->getDonneesFormulaire()->getFileContent('sae_config'), true) ?: [];
        $fluxData->setMetadata($metadata);

        $bordereau = $sedaNG->getBordereauNG($fluxData);
        $this->getDonneesFormulaire()->addFileFromData('sae_bordereau', "bordereau.xml", $bordereau);
        $transferId = $sae->getTransferId($bordereau);
        $this->getDonneesFormulaire()->setData("sae_transfert_id", $transferId);

        try {
            $sedaNG->validateBordereau($bordereau);
        } catch (Exception $e) {
            $message = $e->getMessage() . " : <br/><br/>";
            foreach ($sedaNG->getLastValidationError() as $erreur) {
                $message .= $erreur->message . "<br/>";
            }
            throw new Exception($message);
        }

        $archive_path = $tmp_folder . "/archive.tar.gz";
        $sedaNG->generateArchive($fluxData, $archive_path);

        $transferId = $sae->getTransferId($bordereau);
        $this->getDonneesFormulaire()->setData("sae_transfert_id", $transferId);
        $this->getDonneesFormulaire()->addFileFromData('sae_bordereau', "bordereau.xml", $bordereau);
        $this->getDonneesFormulaire()->addFileFromCopy('sae_archive', "archive.tar.gz", $archive_path);

        $result = $sae->sendArchive($bordereau, $archive_path);

        if (! $result) {
            $this->setLastMessage("L'envoi du bordereau a échoué : " . $sae->getLastError());
            return false;
        }

        $this->addActionOK("Le document a été envoyé au SAE");
        $this->notify($this->action, $this->type, "Le document a été envoyé au SAE");
        return true;
    }

    private function createJournal()
    {
        $journal = $this->getJournal()->getAll($this->id_e, false, $this->id_d, 0, 0, 10000);
        foreach ($journal as $i => $journal_item) {
            $journal[$i]['preuve'] = base64_encode($journal[$i]['preuve']);
        }

        $date_journal_debut = $journal[count($journal) - 1]['date'];
        $date_cloture_journal = $journal[0]['date'];
        //$journal = utf8_encode_array($journal);
        $journal = json_encode($journal);

        $this->getDonneesFormulaire()->addFileFromData('journal', 'journal.json', $journal);
        $this->getDonneesFormulaire()->setData('date_journal_debut', date("Y-m-d", strtotime($date_journal_debut)));
        $this->getDonneesFormulaire()->setData('date_cloture_journal', date("Y-m-d", strtotime($date_cloture_journal)));
        $this->getDonneesFormulaire()->setData('date_cloture_journal_iso8601', date('c', strtotime($date_cloture_journal)));
    }
}
