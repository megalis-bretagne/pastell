<?php

class Reopen extends ActionExecutor
{
    public function go()
    {
        $this->objectInstancier->getInstance(ActionChange::class)
            ->removeLastAction($this->id_d, $this->id_e, $this->id_u);
        $this->setLastMessage("Le dossier a été rouvert, l'état terminé a été supprimé.");
        return true;
    }
}
