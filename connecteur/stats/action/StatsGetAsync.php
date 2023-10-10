<?php

class StatsGetAsync extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go(): bool
    {
        /** @var Stats $connector */
        $connector = $this->getMyConnecteur();
        $connector->fieldVerification();
        $jobManager = $this->objectInstancier->getInstance(JobManager::class);
        $jobManager->setJobForConnecteur(
            $this->id_ce,
            'get_stats',
            'La récupération des statistiques va être déclenchée en tâche de fond'
        );
        $this->setLastMessage('Déclenchement asynchrone de la récupération des statistiques');
        return true;
    }
}
