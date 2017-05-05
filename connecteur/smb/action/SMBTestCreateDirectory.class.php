<?php

class SMBTestCreateDirectory extends ActionExecutor {

    public function go(){
        /** @var ServerMessageBlock $smb */
        $smb = $this->getMyConnecteur();
        $root_folder = $smb->getRootFolder();


        /** @var PasswordGenerator $passwordGenerator */
        $passwordGenerator = $this->objectInstancier->getInstance("PasswordGenerator");

        $folder_name = $passwordGenerator->getPassword();

        $result = $smb->createFolder($folder_name,false,false);
        if (! $result){
            throw new Exception("La création du répertoire a échoué et aucune erreur n'est disponible...");
        }

        $result = $smb->addDocument("test.txt",false,false,"Contenu de mon test",$folder_name);
        if (! $result){
            throw new Exception("La création du document a échoué et aucune erreur n'est disponible...");
        }


        $this->setLastMessage("Création ok !");
        return true;
    }
}