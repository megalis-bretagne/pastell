<?php

class webdavCreateDir extends ActionExecutor {

    public function go(){
        /** @var GEDSSH $sshConnecteur */
        $Connecteur = $this->getMyConnecteur();

        $directory = $Connecteur->testCreateDirAndFile();
        $this->setLastMessage("Cr�ation du fichier $directory : OK");
        return true;
    }

}