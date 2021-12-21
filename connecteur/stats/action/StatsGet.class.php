<?php

class StatsGet extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go()
    {
        /** @var Stats $connecteur */
        $connecteur = $this->getMyConnecteur();

        $connecteur->getStats();

        $this->setLastMessage('Récupération des statistiques terminée.');
        return true;
    }
}
