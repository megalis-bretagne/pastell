<?php

class StatsGet extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go(): bool
    {
        /** @var Stats $connecteur */
        $connecteur = $this->getMyConnecteur();
        $connecteur->fieldVerification();

        $connecteur->getStats();

        $this->setLastMessage('Récupération des statistiques terminée.');
        return true;
    }
}
