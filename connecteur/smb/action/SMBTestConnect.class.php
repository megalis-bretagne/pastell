<?php

class SMBTestConnect extends ActionExecutor {

    public function go(){
        /** @var ServerMessageBlock $smb */
        $smb = $this->getMyConnecteur();
        $root_folder = $smb->getRootFolder();

        $result = $smb->listFolder($root_folder);

        if (! $result){
            throw new Exception("La connexion a échoué et aucune erreur n'est disponible...");
        }

        $this->setLastMessage("Connexion réussie. Contenu du répertoire : ".implode(", ",$result));
        return true;
    }
}