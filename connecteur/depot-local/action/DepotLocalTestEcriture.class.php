<?php

class DepotLocalTestEcriture extends ConnecteurTypeActionExecutor {

    public function go(){
        /** @var DepotLocal $gedLocal */
        $gedLocal = $this->getMyConnecteur();
        $result = $gedLocal->testEcriture();
        $this->setLastMessage($result);
        return true;
    }

}