<?php

class RecupParapheurTestConnexion extends ActionExecutor
{
    public function go()
    {
        /** @var RecupParapheur $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        $message = $recupParapheur->testConnexion();
        $this->setLastMessage($message);
        return true;
    }
}
