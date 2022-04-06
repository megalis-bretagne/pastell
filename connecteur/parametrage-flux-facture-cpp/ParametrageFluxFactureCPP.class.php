<?php

class ParametrageFluxFactureCPP extends Connecteur
{
    private const NB_JOUR_AVANT_SUPP_DEFAULT = 90;

    private $envoi_visa;
    private $iparapheur_type;
    private $iparapheur_sous_type;
    private $envoi_ged;
    private $envoi_sae;
    private $check_mise_a_dispo_gf;
    private $envoi_auto;
    private $nb_jours_avant_supp;

    /** @var  DonneesFormulaire */
    private $donnesFormulaire;

    /**
     * @param DonneesFormulaire $donneesFormulaire
     */
    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->envoi_visa = $donneesFormulaire->get('envoi_visa');
        $this->iparapheur_type = $donneesFormulaire->get('iparapheur_type');
        $this->iparapheur_sous_type = $donneesFormulaire->get('iparapheur_sous_type');
        $this->envoi_ged = $donneesFormulaire->get('envoi_ged');
        $this->envoi_sae = $donneesFormulaire->get('envoi_sae');
        $this->check_mise_a_dispo_gf = $donneesFormulaire->get('check_mise_a_dispo_gf');
        $this->envoi_auto = $donneesFormulaire->get('envoi_auto');
        $this->nb_jours_avant_supp = $donneesFormulaire->get('nb_jours_avant_supp');
        $this->donnesFormulaire = $donneesFormulaire;
    }

    /**
     * @return array
     */
    public function getParametres()
    {
        return [
        "envoi_visa" => $this->envoi_visa,
            "iparapheur_type" => $this->iparapheur_type,
            "iparapheur_sous_type" => $this->iparapheur_sous_type,
            "envoi_ged" => $this->envoi_ged,
            "envoi_sae" => $this->envoi_sae,
            "check_mise_a_dispo_gf" => $this->check_mise_a_dispo_gf,
            "envoi_auto" => $this->envoi_auto
        ];
    }

    /**
     * @return int
     */
    public function getNbJourAvantSupp()
    {
        if ($this->nb_jours_avant_supp) {
            return $this->nb_jours_avant_supp;
        }
        return self::NB_JOUR_AVANT_SUPP_DEFAULT;
    }

    /**
     * @return string
     */
    public function getGedListeStatuts()
    {
        return $this->donnesFormulaire->get('ged_liste_statuts') ?: "MISE_A_DISPOSITION;COMPLETEE;SERVICE_FAIT;MANDATEE";
    }
}
