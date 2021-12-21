<?php

class PurgePurger extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var Purge $connecteur */
        $connecteur = $this->getMyConnecteur();
        $connecteur->purger();

        $this->setLastMessage($connecteur->getLastMessage());
        return true;
    }
}
