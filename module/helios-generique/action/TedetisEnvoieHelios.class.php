<?php

class TedetisEnvoieHelios extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $donneesFormulaire = $this->getDonneesFormulaire();

        if (! $donneesFormulaire->get('envoi_signature') && ! $donneesFormulaire->get('fichier_pes_signe')) {
            $fichier_pes = $donneesFormulaire->getFileContent('fichier_pes');
            $file_name = $donneesFormulaire->get('fichier_pes');
            $donneesFormulaire->addFileFromData('fichier_pes_signe', $file_name[0], $fichier_pes);
        }

        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur("TdT");
        try {
            $file = new Fichier();
            $file->filepath = $donneesFormulaire->getFilePath('fichier_pes_signe');
            $file->filename = $donneesFormulaire->getFileName('fichier_pes_signe');
            $file->content = $donneesFormulaire->getFileContent('fichier_pes_signe');
            $file->contentType = $donneesFormulaire->getContentType('fichier_pes_signe');
            $transactionId = $tdT->sendHelios($file);
            $donneesFormulaire->setData('tedetis_transaction_id', $transactionId);
        } catch (Exception $exception) {
            if (preg_match("#Doublon#i", $exception->getMessage())) {
                $message = $exception->getMessage();
                $this->setLastMessage($message);
                $this->changeAction('tdt-error', $message);
                $this->notify('tdt-error', $this->type, $message);
                return false;
            }
            throw $exception;
        }
        $this->addActionOK("Le document a été envoyé au TdT");
        $this->notify($this->action, $this->type, "Le document a été envoyé au TdT");

        return true;
    }
}
