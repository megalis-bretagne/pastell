<?php

class GEDFTPCreateDir extends ActionExecutor {

    public function go(){
        /** @var GEDSSH $sshConnecteur */
        $ftpConnecteur = $this->getMyConnecteur();

        $directory = $ftpConnecteur->testCreateDirAndFile();
        $this->setLastMessage("Cr�ation du fichier $directory : OK");
        return true;
    }

}