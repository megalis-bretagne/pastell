<?php

declare(strict_types=1);

class RecupParapheurCorbeilleRecup extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go(): bool
    {
        /** @var RecupParapheurCorbeille $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        $id_d = $recupParapheur->recupOne();
        $this->setLastMessage('Création des documents : ' . implode(', ', $id_d));
        return true;
    }
}
