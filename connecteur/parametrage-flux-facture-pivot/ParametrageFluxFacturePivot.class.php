<?php

class ParametrageFluxFacturePivot extends Connecteur
{
    /** @var  DonneesFormulaire */
    private $donneesFormulaire;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->donneesFormulaire = $donneesFormulaire;
    }

    public function getParametres()
    {
        $parametres = array();
        foreach (array('siret','service_destinataire','facture_devise','destinataire') as $id) {
            $parametres[$id] = $this->donneesFormulaire->get($id);
        }
        return $parametres;
    }
}
