<?php

abstract class DossierMarcheCommonEnvoieSAE extends ActionExecutor
{

    public const ACTION_NAME = 'send-archive';
    public const ACTION_NAME_ERROR = 'erreur-envoie-sae';

    abstract public function getFluxDataClassName();

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

            $tmpFolder->delete($tmp_folder);
        } catch (Exception $e) {
            $tmpFolder->delete($tmp_folder);
            throw $e;
        }

        $tmpFolder->delete($tmp_folder);

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

        $this->getDonneesFormulaire()->setData("sae_show", true);

        /** @var SEDANG $sedaNG */

        $sedaNG = $this->getConnecteur('Bordereau SEDA');

        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');

        $fluxDataClassName = $this->getFluxDataClassName();

        /** @var FluxData $fluxData */
        $fluxData = new $fluxDataClassName(
            $this->getDonneesFormulaire()
        );

        $metadata = json_decode($this->getDonneesFormulaire()->getFileContent('sae_config'), true) ?: array();
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
            throw new UnrecoverableException($message);
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

        $this->addActionOK("Le dossier a été envoyé au SAE");
        $this->notify($this->action, $this->type, "Le dossier a été envoyé au SAE");
        return true;
    }
}
