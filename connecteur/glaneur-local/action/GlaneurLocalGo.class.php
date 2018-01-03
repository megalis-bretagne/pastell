<?php

class GlaneurLocalGo extends ActionExecutor {

    /**
     * @return bool
     * @throws Exception
     */
    public function go(){
        /** @var GlaneurLocal $connecteur */
        $connecteur = $this->getMyConnecteur();

        $connecteur->glaner();
        $this->setLastMessage(implode("<br/>",$connecteur->getLastMessage()));
        return true;

    }
}