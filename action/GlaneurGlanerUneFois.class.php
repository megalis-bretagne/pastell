<?php

class GlaneurGlanerUneFois extends ActionExecutor {

    /**
     * @throws Exception
     */
    public function go(){
        /** @var GlaneurLocal $connecteur */
        $connecteur = $this->getMyConnecteur();
        $result = $connecteur->glaner();
        $this->setLastMessage(implode("<br/>",$connecteur->getLastMessage()));
        return $result;
    }

}