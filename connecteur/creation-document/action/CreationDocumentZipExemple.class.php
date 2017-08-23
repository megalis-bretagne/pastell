<?php

class CreationDocumentZipExemple extends ActionExecutor {

    public function go(){
        $connecteur = $this->getMyConnecteur();
        $result = $connecteur->recupFileExemple($this->id_e);
        if ($result){
            $this->setLastMessage($result);
        } else {
            $this->setLastMessage("Aucun fichier trouvé");
        }
        return true;
    }

}