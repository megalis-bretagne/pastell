<?php

class AsalaeRestPing extends ActionExecutor
{
    public function go()
    {
        /** @var AsalaeREST $asalae */
        $asalae = $this->getMyConnecteur();
        $message = $asalae->ping();
        $this->setLastMessage($message);
        return true;
    }
}
