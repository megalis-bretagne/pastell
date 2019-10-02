<?php
class S2lowMAJCertif extends ChoiceActionExecutor {

    /**
     * @return bool
     * @throws Exception
     */
    public function go(){
        $recuperateur = $this->getRecuperateur();
        $id_ce_list = $recuperateur->get('id_ce_list');
        if (!$id_ce_list){
            $this->displayErrorAndRedirect("Aucun connecteur sélectionné");
        }

        $fileUploader = new FileUploader();

        $user_certificate = $fileUploader->getFileContent('user_certificat');

        if (! $user_certificate){
            $this->displayErrorAndRedirect("Il faut choisir un certificat");
        }

        foreach($id_ce_list as $id_ce){
            $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
            if($user_certificate){
                $user_certificate_name = $fileUploader->getName('user_certificat');
                $user_certificat_password = $recuperateur->get('user_certificat_password');
                $connecteurConfig->addFileFromData('user_certificat', $user_certificate_name, $user_certificate);
                $connecteurConfig->setData('user_certificat_password', $user_certificat_password);
                $this->objectInstancier->ActionExecutorFactory->executeOnConnecteur($id_ce,$this->id_u,'update-certificate');
            }
        }
        $lastMessage = $this->objectInstancier->getInstance(LastMessage::class);
        $lastMessage->setLastMessage("Le certificat a été remplacé");
        $this->redirect("/Connecteur/externalData?id_ce={$this->id_ce}&field=changement_certificat");
        return true;
    }

    public function displayAPI(){
        return array();
    }

    public function display(){
        $all_connecteur = array();
        foreach($this->objectInstancier->ConnecteurEntiteSQL->getAllById('s2low') as $connecteur){
            if (! $connecteur['id_e']){
                continue;
            }
            $all_connecteur[] = $connecteur;
        }

        $this->all_connecteur = $all_connecteur;

        $this->renderPage("Mise à jour certificat S2low", __DIR__."/../template/S2lowChoixMAJCertificat.php");
        return true;
    }

    private function displayErrorAndRedirect($error_message){
        $lastError = $this->objectInstancier->getInstance(LastError::class);
        $lastError->setLastMessage($error_message);
        $this->redirect("/Connecteur/externalData?id_ce={$this->id_ce}&field=changement_certificat");
    }

}