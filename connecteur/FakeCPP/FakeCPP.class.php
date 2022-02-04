<?php

class FakeCPP extends PortailFactureConnecteur
{
    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        //NOTHING TO DO
    }

    /**
     * @return false|mixed|string
     * @throws CPPException
     */
    protected function rechercheFactureParRecipiendaire(
        string $idFournisseur = "",
        string $periodeDateHeureEtatCourantDu = "",
        string $periodeDateHeureEtatCourantAu = ""
    ) {
        $ListeFacturesCPP = file_get_contents(__DIR__ . "/fixtures/CPPListeFactures.json");
        if (!$ListeFacturesCPP) {
            throw new CPPException("Problème lors de la récuperation de la liste des factures cpp");
        }
        $ListeFacturesCPP = json_decode($ListeFacturesCPP, true);

        $connecteur_info = $this->getConnecteurInfo();
        $id_e = $connecteur_info['id_e'];

        foreach ($ListeFacturesCPP['listeFactures'] as $i => $factureCPP) {
            $ListeFacturesCPP['listeFactures'][$i]['idFacture'] = $id_e . "-" . $factureCPP['idFacture'];
        }

        if (!$ListeFacturesCPP) {
            throw new CPPException("La liste des factures cpp n'est pas lisible");
        }
        return $ListeFacturesCPP;
    }

    /**
     * @param $idFacture
     * @param int $nbResultatsMaximum
     * @return false|mixed|string
     * @throws CPPException
     */
    protected function consulterHistoriqueFacture($idFacture, $nbResultatsMaximum = 50)
    {
        $HistoStatutFactureCPP = file_get_contents(__DIR__ . "/fixtures/CPPHistoStatutFacture.json");
        if (!$HistoStatutFactureCPP) {
            throw new CPPException("Problème lors de la récuperation de l'historique statut de la facture cpp " . $idFacture);
        }
        $HistoStatutFactureCPP = json_decode($HistoStatutFactureCPP, true);
        if (!$HistoStatutFactureCPP) {
            throw new CPPException("L'historique statut de la facture cpp n'est pas lisible. Identifiant facture: " . $idFacture);
        }
        return $HistoStatutFactureCPP;
    }

    /**
     * @param $format
     * @param $idFacture
     * @return false|mixed|string
     * @throws CPPException
     */
    protected function telechargerGroupeFacture($format, $idFacture)
    {
        list($id_e,$numFacture) = explode("-", $idFacture);

        $PathFichierFactureCPP = __DIR__ . "/fixtures/facture_{$numFacture}.xml";
        if (!$PathFichierFactureCPP) {
            throw new CPPException("Problème lors de la récuperation du fichier de la facture cpp " . $idFacture);
        }
        return file_get_contents($PathFichierFactureCPP);
    }

    /**
     * @param $idFacture
     * @param $idNouveauStatut
     * @param string $motif
     * @param string $numeroMandat
     * @return false|mixed|string
     * @throws CPPException
     */
    protected function traiterFactureRecue($idFacture, $idNouveauStatut, $motif = "", $numeroMandat = "")
    {
        $ResultStatutFactureCPP = file_get_contents(__DIR__ . "/fixtures/CPPResultStatutOK.json");
        if (!$ResultStatutFactureCPP) {
            throw new CPPException("Problème lors du changement de statut de la facture cpp " . $idFacture);
        }
        $ResultStatutFactureCPP = json_decode($ResultStatutFactureCPP, true);
        if (!$ResultStatutFactureCPP) {
            throw new CPPException("Le résultat du changement de statut de la facture cpp " . $idFacture . " n'est pas lisible.");
        }
        return $ResultStatutFactureCPP;
    }

    public function getNoChangeStatutChorus()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function getNoRecupFacture()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function getDateDepuisLe()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function rechercheFactureTravaux(
        string $periodeDateHeureEtatCourantDu = "",
        string $periodeDateHeureEtatCourantAu = ""
    ) {
        // TODO: Implement rechercheFactureTravaux() method.
    }
}
