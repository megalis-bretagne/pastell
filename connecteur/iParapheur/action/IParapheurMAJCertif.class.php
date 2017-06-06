<?php
class IParapheurMAJCertif extends ChoiceActionExecutor {


    public function go(){
        $recuperateur = new Recuperateur($_POST);

        $go =$recuperateur->get('go');
        if (! $go){
            $this->display();
        }

        return $this->updateCertificate();
    }

    public function displayAPI(){
        return array();
    }

    public function display(){
        $all_connecteur = array();
        foreach($this->objectInstancier->ConnecteurEntiteSQL->getAllById('iParapheur') as $connecteur){
            if (! $connecteur['id_e']){
                continue;
            }
            $all_connecteur[] = $connecteur;
        }

        $this->all_connecteur = $all_connecteur;

        $this->renderPage("Mise à jour certificat iParapheur", __DIR__."/../template/IParapheurChoixMAJCertificat.php");
        exit;
    }

    private function updateCertificate(){
        $recuperateur = new Recuperateur($_POST);
        $id_ce_list = $recuperateur->get('id_ce_list');
        if (!$id_ce_list){
            throw new Exception("Aucun connecteur selectionné");
        }

        $fileUploader = new FileUploader();
        $user_certificate = $fileUploader->getFileContent('user_certificat');

        if (! $user_certificate){
            throw new Exception("Il faut sélectionné un certificat");
        }

        foreach($id_ce_list as $id_ce){
            $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
            if($user_certificate){
                $user_certificate_name = $fileUploader->getName('user_certificat');
                $user_certificat_password = $recuperateur->get('user_certificat_password');
                $connecteurConfig->addFileFromData('iparapheur_user_certificat', $user_certificate_name, $user_certificate);
                $connecteurConfig->setData('iparapheur_user_certificat_password', $user_certificat_password);
                $this->objectInstancier->ActionExecutorFactory->executeOnConnecteur($id_ce,$this->id_u,'update-certificate');
            }
        }

        $this->setLastMessage("Le(s) certificat(s) a été remplacé(s)");
        return true;
    }


}