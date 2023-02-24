<?php

class CPPImporterFactureAsynchrone extends ActionExecutor
{
    public function go()
    {
        try {
            $this->objectInstancier->getInstance(JobManager::class)->setJobForConnecteur(
                $this->id_ce,
                'import-facture',
                "programmation de l'import facture"
            );
        } catch (Exception $e) {
            $this->setLastMessage("\"L'action n'a pas pu s'exécuter. Erreur : {$e->getMessage()}");
            return false;
        }
        $this->setLastMessage("Programmation de l'importation des factures");
        return true;
    }
}
