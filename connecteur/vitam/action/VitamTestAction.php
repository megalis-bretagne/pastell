<?php

declare(strict_types=1);

final class VitamTestAction extends ActionExecutor
{
    /**
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function go()
    {
        /** @var VitamConnector $connector */
        $connector = $this->getMyConnecteur();

        $connector->testConnection();

        $this->setLastMessage('La connexion est réussie');
        return true;
    }
}
