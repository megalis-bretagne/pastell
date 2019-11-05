<?php

class ParametrageFluxPdfGenerique extends Connecteur
{

    private $envoi_signature;
    private $iparapheur_type;
    private $iparapheur_sous_type;
    private $envoi_ged_1;
    private $envoi_mailsec;
    private $to;
    private $envoi_ged_2;
    private $envoi_sae;


    /** @var  DonneesFormulaire */
    private $donnesFormulaire;


    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {

        $this->envoi_signature = $donneesFormulaire->get('envoi_signature');
        $this->iparapheur_type = $donneesFormulaire->get('iparapheur_type');
        $this->iparapheur_sous_type = $donneesFormulaire->get('iparapheur_sous_type');
        $this->envoi_ged_1 = $donneesFormulaire->get('envoi_ged_1');
        $this->envoi_mailsec = $donneesFormulaire->get('envoi_mailsec');
        $this->to = $donneesFormulaire->get('to');
        $this->envoi_ged_2 = $donneesFormulaire->get('envoi_ged_2');
        $this->envoi_sae = $donneesFormulaire->get('envoi_sae');
        $this->donnesFormulaire = $donneesFormulaire;
    }

    public function getParametres()
    {
        $parametres = array("envoi_signature" => $this->envoi_signature,
            "iparapheur_type" => $this->iparapheur_type,
            "iparapheur_sous_type" => $this->iparapheur_sous_type,
            "envoi_ged_1" => $this->envoi_ged_1,
            "envoi_mailsec" => $this->envoi_mailsec,
            "to" => $this->to,
            "envoi_ged_2" => $this->envoi_ged_2,
            "envoi_sae" => $this->envoi_sae);
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
