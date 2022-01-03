<?php

class TestConnecteur extends Connecteur
{
    /** @var DonneesFormulaire */
    private $connecteurConfig;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    /**
     * @return mixed
     */
    public function getChamps1()
    {
        return $this->connecteurConfig->get('champs1');
    }
}
