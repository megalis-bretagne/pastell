<?php

class GlaneurGlanerFichierExemple extends ActionExecutor {

    /**
     * @throws Exception
     */
    public function go(){
        /** @var GlaneurLocal $connecteur */
        $connecteur = $this->getMyConnecteur();

        $connecteur->glanerFicExemple();
        $this->setLastMessage(implode("<br/>",$connecteur->getLastMessage()));
        return true;
    }

}