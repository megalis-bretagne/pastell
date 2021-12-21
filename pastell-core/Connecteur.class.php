<?php

abstract class Connecteur
{
    protected $lastError;
    /**
     * @var DonneesFormulaire
     */
    private $docDonneesFormulaire;
    private $connecteurInfo;

    abstract public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire);

    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @return DonneesFormulaire
     * Retourne les données du flux en cours de traitement.
     * Le connecteur ne doit accéder qu'aux seuls attributs à sa portée :
     * - attributs publics : déclarés dans le flux
     * - attributs privés : déclarés par le connecteur lui-même
     * Il ne doit pas accéder aux attributs déclarés par d'autres connecteurs.
     */
    public function getDocDonneesFormulaire()
    {
        return $this->docDonneesFormulaire;
    }

    public function setDocDonneesFormulaire(DonneesFormulaire $docDonneesFormulaire)
    {
        $this->docDonneesFormulaire = $docDonneesFormulaire;
    }

    /**
     * @return array information sur le connecteur (id_ce, id_e,...)
     */
    public function getConnecteurInfo()
    {
        return $this->connecteurInfo;
    }

    public function setConnecteurInfo(array $connecteur_info)
    {
        $this->connecteurInfo = $connecteur_info;
    }

    public function isGlobal()
    {
        return $this->connecteurInfo['id_e'] == 0;
    }

    private $logger;
    public function setLogger(Monolog\Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
