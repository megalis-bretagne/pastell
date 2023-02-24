<?php

use Pastell\Connector\AbstractSedaGeneratorConnector;

class SedaGeneriqueTestConnexion extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go(): bool
    {
        /** @var AbstractSedaGeneratorConnector $sedaGenerique */
        $sedaGenerique = $this->getMyConnecteur();
        try {
            $result = $sedaGenerique->testConnexion();
        } catch (Exception $e) {
            $this->setLastMessage(substr($e->getMessage(), 0, 200));
            return false;
        }
        $this->setLastMessage("La connexion est réussie " . $result);
        return true;
    }
}
