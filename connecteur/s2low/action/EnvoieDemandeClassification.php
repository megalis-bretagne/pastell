<?php

class EnvoieDemandeClassification extends ActionExecutor
{
    public function go()
    {
        /** @var S2low $s2low */
        $s2low = $this->getMyConnecteur();
        $result = $s2low->demandeClassification();
        $this->setLastMessage($result);
        return true;
    }
}
