<?php

declare(strict_types=1);

class RecupParapheurCorbeilleTestConnexion extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go(): bool
    {
        /** @var RecupParapheurCorbeille $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        $message = $recupParapheur->testConnexion();
        $this->setLastMessage($message);
        return true;
    }
}
