<?php

class GlaneurLocalGlaner extends ActionExecutor {

    /**
     * @throws Exception
     */
    public function go(){
        /** @var GlaneurLocal $connecteur */
        $connecteur = $this->getMyConnecteur();

        $result = $connecteur->glaner();
        //echo "$result";
        $this->setLastMessage(implode("<br/>",$connecteur->getLastMessage()));
        return $result;
    }

}