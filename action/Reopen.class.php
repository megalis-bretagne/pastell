<?php

use Pastell\Service\Action\Reopen as ReopenServices;

class Reopen extends ActionExecutor
{
    /**
     * @throws UnrecoverableException
     */
    public function go()
    {
        $this->objectInstancier->getInstance(ReopenServices::class)
            ->reopen($this->id_e, $this->id_d, $this->id_u);
        $this->setLastMessage("Le dossier a été rouvert, l'état terminé a été supprimé.");
        return true;
    }
}
