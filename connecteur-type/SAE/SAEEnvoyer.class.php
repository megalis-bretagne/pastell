<?php

class SAEEnvoyer extends ConnecteurTypeActionExecutor {


    const ACTION_NAME = 'send-archive';
    const ACTION_NAME_ERROR = 'erreur-envoie-sae';

    /**
     * @return bool
     * @throws Exception
     */
    public function go(){

        /** @var TmpFolder $tmpFolder */
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        $result = false;

        try {
            $result = $this->goThrow($tmp_folder);
        } catch (UnrecoverableException $e){
            $this->changeAction(self::ACTION_NAME_ERROR,$e->getMessage());
            $this->notify(self::ACTION_NAME_ERROR,$this->type,$e->getMessage());

            $tmpFolder->delete($tmp_folder);
        } catch (Exception $e){
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
    public function goThrow($tmp_folder){

        /** @var SEDANG $sedaNG */
        $sedaNG = $this->getConnecteur('Bordereau SEDA');

        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');

        $donneesFormulaire = $this->getDonneesFormulaire();

        $sae_show = $this->getMappingValue('sae_show');
        $sae_bordereau = $this->getMappingValue('sae_bordereau');
        $sae_archive = $this->getMappingValue('sae_archive');
        $sae_transfert_id = $this->getMappingValue('sae_transfert_id');


        $fluxDataClassName = $this->getDataSedaClassName();
        $fluxDataClassPath = $this->getDataSedaClassPath();

        if (! $fluxDataClassPath){
            $this->setLastMessage("La classe ".$fluxDataClassName." est manquante.");
            return false;
        }

        require_once $fluxDataClassPath;
        /** @var FluxData $fluxData */
        $fluxData = new $fluxDataClassName(
            $donneesFormulaire
        );

        $donneesFormulaire->setData($sae_show,true);

        $bordereau = $sedaNG->getBordereauNG($fluxData);
        $donneesFormulaire->addFileFromData($sae_bordereau,"bordereau.xml",$bordereau);
        $transferId = $sae->getTransferId($bordereau);
        $donneesFormulaire->setData($sae_transfert_id,$transferId);

        try {
            $sedaNG->validateBordereau($bordereau);
        } catch (Exception $e) {
            $message = $e->getMessage()." : <br/><br/>";
            foreach($sedaNG->getLastValidationError() as $erreur){
                $message .= $erreur->message."<br/>";
            }
            throw new UnrecoverableException($message);
        }

        $archive_path = $tmp_folder."/archive.tar.gz";

        $sedaNG->generateArchive($fluxData,$archive_path);

        $transferId = $sae->getTransferId($bordereau);
        $donneesFormulaire->setData($sae_transfert_id,$transferId);
        $donneesFormulaire->addFileFromData($sae_bordereau,"bordereau.xml",$bordereau);
        $donneesFormulaire->addFileFromCopy($sae_archive,"archive.tar.gz",$archive_path);

        $result = $sae->sendArchive($bordereau,$archive_path);

        if (! $result){
            $this->setLastMessage("L'envoi du bordereau a échoué : " . $sae->getLastError());
            return false;
        }

        $this->addActionOK("Le document a été envoyé au SAE");
        $this->notify($this->action, $this->type,"Le document a été envoyé au SAE");
        return true;
    }


}