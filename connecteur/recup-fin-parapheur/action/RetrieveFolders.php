<?php

declare(strict_types=1);

class RetrieveFolders extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go()
    {
        /** @var RecupFinParapheur $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        $id_d = $recupParapheur->recupOne();
        $this->setLastMessage('Création des documents : ' . implode(', ', $id_d));
        return true;
    }
}
