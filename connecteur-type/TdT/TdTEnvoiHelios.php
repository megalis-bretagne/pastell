<?php

class TdTEnvoiHelios extends ConnecteurTypeActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {

        $tdt_error = $this->getMappingValue('tdt-error');

        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur("TdT");
        try {
            $fichierHelios = new Fichier();
            $fichierHelios->filepath = $this->getDonneesFormulaire()->getFilePath($this->getMappingValue('fichier_pes'));
            $fichierHelios->filename = $this->getDonneesFormulaire()->getFileName($this->getMappingValue('fichier_pes'));
            $fichierHelios->content = $this->getDonneesFormulaire()->getFileContent($this->getMappingValue('fichier_pes'));
            $fichierHelios->contentType = $this->getDonneesFormulaire()->getContentType($this->getMappingValue('fichier_pes'));
            $tdt_transaction_id = $tdT->sendHelios($fichierHelios);

            $this->getDonneesFormulaire()->setData(
                $this->getMappingValue('pes_tedetis_transaction_id'),
                $tdt_transaction_id
            );

            $this->getDonneesFormulaire()->setData(
                $this->getMappingValue('pes_has_reponse'),
                'true'
            );
        } catch (Exception $exception) {
            if (preg_match("#Doublon#i", $exception->getMessage())) {
                $message = $exception->getMessage();
                $this->setLastMessage($message);
                $this->changeAction($tdt_error, $message);
                $this->notify($tdt_error, $this->type, $message);
                return false;
            }
            throw $exception;
        }
        $this->addActionOK("Le document a été envoyé au TdT");
        $this->notify($this->action, $this->type, "Le document a été envoyé au TdT");

        return true;
    }
}
