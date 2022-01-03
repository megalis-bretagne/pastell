<?php

class AsalaeRestVersion extends ActionExecutor
{
    public function go()
    {
        /** @var AsalaeREST $asalae */
        $asalae = $this->getMyConnecteur();
        $message = $asalae->getVersion();
        $this->setLastMessage("Connexion réussie: " . json_encode($message));
        return true;
    }
}
