<?php

class webdavCreateDir extends ActionExecutor {

    public function go(){
        /** @var GEDSSH $sshConnecteur */
        $Connecteur = $this->getMyConnecteur();

        $directory = $Connecteur->testCreateDirAndFile();
        $this->setLastMessage("Création du fichier $directory : OK");
        return true;
    }

}