<?php

class DepotLocalTestLecture extends ConnecteurTypeActionExecutor {

    public function go(){
        /** @var DepotLocal $gedLocal */
        $gedLocal = $this->getMyConnecteur();
        $result = $gedLocal->testLecture();
        $this->setLastMessage($result);
        return true;
    }

}