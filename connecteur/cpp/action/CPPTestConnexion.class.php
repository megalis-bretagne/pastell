<?php

class CPPTestConnexion extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var CPP $cpp */
        $cpp = $this->getMyConnecteur();
        $result = $cpp->testConnexion();
        if (! $result) {
            $this->setLastMessage("La connexion cpp a échoué : " . $cpp->getLastError());
            return false;
        }
        if ($cpp->getIsRaccordementCertificat()) {
            $this->setLastMessage("La connexion avec le raccordement par certificat est réussie. Attention !!! elle est dépréciée, l'AIFE permet cette authentification jusqu'à fin 2020. Veuillez utiliser l'authentification Oauth PISTE.");
            return false;
        }
        $this->setLastMessage("La connexion est réussie");
        return true;
    }
}
