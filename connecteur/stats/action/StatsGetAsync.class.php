<?php

class StatsGetAsync extends ActionExecutor
{
    public function go()
    {
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
