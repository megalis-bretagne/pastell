<?php

class RecupPESRetour extends ActionExecutor
{
    public function go()
    {
        /** @var S2low $s2low */
        $s2low = $this->getMyConnecteur();
        $s2low->getPESRetourListe();
        $this->setLastMessage("Les fichiers Hélios PES Retour ont été récupérés");
        return true;
    }
}
