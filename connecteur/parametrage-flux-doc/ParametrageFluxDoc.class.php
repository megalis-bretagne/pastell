<?php

class ParametrageFluxDoc extends Connecteur
{

    private $envoi_signature;
    private $iparapheur_type;
    private $iparapheur_sous_type;
    private $envoi_ged;
    private $envoi_auto;

    /** @var  DonneesFormulaire */
    private $donnesFormulaire;


    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {

        $this->envoi_signature = $donneesFormulaire->get('envoi_signature');
        $this->iparapheur_type = $donneesFormulaire->get('iparapheur_type');
        $this->iparapheur_sous_type = $donneesFormulaire->get('iparapheur_sous_type');
        $this->envoi_ged = $donneesFormulaire->get('envoi_ged');
        $this->envoi_auto = $donneesFormulaire->get('envoi_auto');
        $this->donnesFormulaire = $donneesFormulaire;
    }

    public function getParametres()
    {
        $parametres = array("envoi_signature" => $this->envoi_signature,
            "iparapheur_type" => $this->iparapheur_type,
            "iparapheur_sous_type" => $this->iparapheur_sous_type,
            "envoi_ged" => $this->envoi_ged,
            "envoi_auto" => $this->envoi_auto);
        return $parametres;
    }

    public function getGedDirectoryName(DonneesFormulaire $donneesFormulaire)
    {
        $format = $this->donnesFormulaire->get('ged_directory_name_format') ?: "%libelle%";
        return preg_replace_callback(
            "#%([^%]*)%#",
            function ($matches) use ($donneesFormulaire) {
                return $donneesFormulaire->get($matches[1]);
            },
            $format
        );
    }
}
