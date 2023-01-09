<?php

class RecupParapheurRecup extends ActionExecutor
{
    public function go()
    {
        /** @var RecupParapheur $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        $id_d = $recupParapheur->recupOne();
        $this->setLastMessage("Création des documents : " . implode(', ', $id_d));
        return true;
    }
}
