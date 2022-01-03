<?php

class CPPListeFacture extends ActionExecutor
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
                $cpp->rechercheFactureParRecipiendaire("", $cpp->getDateDepuisLe())
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
        $result = $this->metier();
        if (! $result) {
            $this->setLastMessage("La connexion cpp a échoué : " . $cpp->getLastError());
            return false;
        }
        $this->setLastMessage("Liste des factures ayant changé de statut depuis le " . $cpp->getDateDepuisLe() . ": " . $result);
        return true;
    }
}
