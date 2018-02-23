<?php

class GlaneurLocalFicExemple extends ActionExecutor {

    public function go(){
        /** @var GlaneurLocal $glaneurLocal */
        $glaneurLocal = $this->getMyConnecteur();
        /*
        $result = $connecteur->recupFileExemple($this->id_e);
        if ($result){
            $this->setLastMessage($result);
        } else {
            $this->setLastMessage("Aucun fichier trouvé");
        }
        */
        return true;
    }

}