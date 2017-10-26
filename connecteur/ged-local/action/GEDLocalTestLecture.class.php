<?php

class GEDLocalTestLecture extends ConnecteurTypeActionExecutor {

    public function go(){
        /** @var GEDLocal $gedLocal */
        $gedLocal = $this->getMyConnecteur();
        $result = $gedLocal->testLecture();
        $this->setLastMessage($result);
        return true;
    }

}