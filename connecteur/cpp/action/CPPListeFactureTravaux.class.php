<?php

class CPPListeFactureTravaux extends ActionExecutor
{

    /**
     * @return UTF8Encoder
     */
    public function getUTF8Encoder()
    {
        return $this->objectInstancier->getInstance(UTF8Encoder::class);
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function metier()
    {
        /** @var CPP $cpp */
        $cpp = $this->getMyConnecteur();
        return $this->getUTF8Encoder()->decode(
            json_encode($this->getUTF8Encoder()->encode(
                $cpp->rechercheFactureTravaux($cpp->getDateDepuisLe())
            ))
        );
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var CPP $cpp */
        $cpp = $this->getMyConnecteur();
        if ($cpp->getIsRaccordementCertificat()) {
            $this->setLastMessage("La récupération des factures de travaux nécessite un raccordement en mode Oauth PISTE");
            return false;
        }
        if (!$this->getConnecteurProperties()->get('user_role')) {
            $this->setLastMessage("Il faut sélectionner le rôle de l'utilisateur pour la récupération des factures de travaux");
            return false;
        }
        $result = $this->metier();
        if (! $result) {
            $this->setLastMessage("La connexion cpp a échoué : " . $cpp->getLastError());
            return false;
        }
        $this->setLastMessage("Liste des factures de travaux ayant changé de statut depuis le " . $cpp->getDateDepuisLe() . ": " . $result);
        return true;
    }
}
