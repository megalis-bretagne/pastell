<?php

class DepotLocalTestEcritureFichier extends ActionExecutor {

    public function go(){
        /** @var DepotLocal $gedLocal */
        $gedLocal = $this->getMyConnecteur();
        $result = $gedLocal->testEcritureFichier();
        $this->setLastMessage($result);
        return true;
    }

}