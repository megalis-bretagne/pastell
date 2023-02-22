<?php

class ParamChorusFournisseur extends Connecteur
{
    private const SYNCHRO_NB_JOUR_MAX_DEFAULT = 30;

    /** @var  DonneesFormulaire */
    private $connecteurConfig;

    /**
     * @param DonneesFormulaire $donneesFormulaire
     */
    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    /**
     * @return array|string
     */
    public function recupStatutFacture()
    {
        return $this->connecteurConfig->get('recup_status_facture');
    }

    /**
     * @return array|int|string
     */
    public function getNbJourMaxSynchro()
    {
        if ($this->connecteurConfig->get('nb_jour_max_synchro')) {
            return $this->connecteurConfig->get('nb_jour_max_synchro');
        }
        return self::SYNCHRO_NB_JOUR_MAX_DEFAULT;
    }
}
