<?php

class PurgeGlobalePurger extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var Purge $connecteur */
        $connecteur = $this->getMyConnecteur();
        $connecteur->purgerGlobal();

        $this->setLastMessage($connecteur->getLastMessage());
        return true;
    }
}
