<?php

class PurgePurgerAsync extends ActionExecutor
{
    public function go()
    {
        $jobManager = $this->objectInstancier->getInstance(JobManager::class);
        $jobManager->setJobForConnecteur($this->id_ce, "purge", "La purge va être déclenchée en tâche de fond");
        $this->setLastMessage("Déclenchement asynchrone de la purge");
        return true;
    }
}
