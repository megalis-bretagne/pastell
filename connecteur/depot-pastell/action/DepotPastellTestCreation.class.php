<?php

class DepotPastellTestCreation extends ActionExecutor
{
    /**
     * @return bool
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function go()
    {
        /** @var DepotPastell $pastellConnecteur */
        $pastellConnecteur = $this->getMyConnecteur();
        $info = $pastellConnecteur->createDocument();
        $this->setLastMessage("Creation du document {$info['id_d']}");
        return true;
    }
}
