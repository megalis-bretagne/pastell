<?php

class GEDLocalTestEcritureFichier extends ActionExecutor {

    public function go(){
        /** @var GEDLocal $gedLocal */
        $gedLocal = $this->getMyConnecteur();
        $result = $gedLocal->testEcritureFichier();
        $this->setLastMessage($result);
        return true;
    }

}