<?php

class HistoStatutCPP
{

    public function create()
    {
        $result = array();
        $result['statut_courant'] = '';
        $result['histo_statut'] = array();
        return json_encode($result);
    }

    public function addStatut($json, $statut_code, $commentaire, $utilisateur_nom, $utilisateur_prenom)
    {
        if (! $json) {
            $json = $this->create();
        }
        $result = json_decode($json);

        $histo_statut = new stdClass();
        $histo_statut->statut_code = $statut_code;
        $histo_statut->statut_date_passage = date("Y-m-d h:i");
        $histo_statut->statut_utilisateur_nom = $utilisateur_nom;
        $histo_statut->statut_utilisateur_prenom = $utilisateur_prenom;
        $histo_statut->statut_commentaire = $commentaire;

        array_unshift($result->histo_statut, $histo_statut);

        $result->statut_courant = $statut_code;

        return json_encode($result);
    }
}
