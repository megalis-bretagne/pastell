<?php

class RecupParapheurRecupAsync extends ActionExecutor
{
    public function go()
    {
        $jobManager = $this->objectInstancier->getInstance(JobManager::class);
        $jobManager->setJobForConnecteur(
            $this->id_ce,
            'recup',
            'La récupération va être déclenchée en tâche de fond'
        );
        $this->setLastMessage('Récupération asynchrone lancée');
        return true;
    }
}
