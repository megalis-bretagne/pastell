<?php

class ChorusProFournisseurRecupCPPFactureId extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var CPP $portailFature */
        $portailFature = $this->getConnecteur("PortailFacture");

        $numero_flux_depot = $this->getDonneesFormulaire()->get('numero_flux_depot');

        $crr_import = $portailFature->consulterCompteRenduImport($numero_flux_depot);

        if (isset($crr_import['etatCourantDepotFlux'])) {
            $this->getDonneesFormulaire()->setData('has_donnees_depot', true);


            $info_from_cpp = array(
                'codeInterfaceDepotFlux' => 'code_interface_flux',
                'etatCourantDepotFlux' => 'etat_courant_flux',
                'dateHeureEtatCourantFlux' => 'date_heure_etat_courant_flux',
                'nomFichier' => 'nom_fichier_flux',
            );

            foreach ($info_from_cpp as $item_chorus => $item_pastell) {
                $this->getDonneesFormulaire()->setData($item_pastell, $crr_import[$item_chorus]);
            }

            $this->getDonneesFormulaire()->addFileFromData(
                'fichier_cr',
                'compte-rendu.json',
                json_encode($crr_import)
            );

            if (in_array($crr_import['etatCourantDepotFlux'], array('IN_REJETE','IN_INCIDENTE'))) {
                $message = "Le flux a été rejeté par CPP : " . json_encode($crr_import);
                $this->setLastMessage($message);
                $this->notify("flux-rejete", $this->type, $message);
                $this->changeAction("flux-rejete", $message);
                return false;
            }
            if (in_array($crr_import['etatCourantDepotFlux'], ['IN_DEPOT_PORTAIL_EN_ATTENTE_TRAITEMENT_SE_CPP'])) {
                $this->setLastMessage("Le flux est en cours d'analyse par Chorus");
                return true;
            }
        }

        $file_name = $this->getDonneesFormulaire()->getFileName('fichier_facture_pdf', 0);
        $file_path = $this->getDonneesFormulaire()->getFilePath('fichier_facture_pdf', 0);
        $this->getDonneesFormulaire()->addFileFromCopy('fichier_original', $file_name, $file_path, 0);

        $result = $portailFature->getInfoByNumeroFluxDepot($numero_flux_depot);

        $this->getDonneesFormulaire()->setData('identifiant_facture_cpp', $result['identifiantFactureCPP']);
        $this->getDonneesFormulaire()->setData('statut_facture', $result['statut']);
        $message = "Identifiant de la facture {$result['identifiantFactureCPP']} récupéré";
        $this->setLastMessage($message);
        $this->addActionOK($message);
        return true;
    }
}
