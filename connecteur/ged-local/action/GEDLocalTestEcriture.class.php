<?php

class GEDLocalTestEcriture extends ConnecteurTypeActionExecutor {

    public function go(){
        /** @var GEDLocal $gedLocal */
        $gedLocal = $this->getMyConnecteur();
        $result = $gedLocal->testEcriture();
        $this->setLastMessage($result);
        return true;
    }

}