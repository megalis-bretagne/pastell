<?php

class CPPDeposerPDF extends ActionExecutor
{
    /**
     * @return bool
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function go()
    {
        /** @var CPP $portailFature */
        $portailFature = $this->getConnecteur("PortailFacture");

        $filename = $this->getDonneesFormulaire()->getFileName('fichier_facture_pdf');
        $filecontent = $this->getDonneesFormulaire()->getFileContent('fichier_facture_pdf');
        $result = $portailFature->deposerPDF($filename, $filecontent);
        $this->getDonneesFormulaire()->setData('has_information', true);


        $info_from_cpp = [
            'codeDestinataire' => 'code_destinataire',
            'codeDeviseFacture' => 'code_devise_facture',
            'codeFournisseur' => 'code_fournisseur',
            'codeRetour' => 'code_retour',
            'codeServiceExecutant' => 'code_service_executant',
            'dateFacture' => 'date_facture',
            'libelle' => 'libelle',
            'montantAPayer' => 'montant_a_payer',
            'montantHtTotal' => 'montant_ht_total',
            'montantTVA' => 'montant_tva',
            'montantTtcAvantRemiseGlobalTTC' => 'montant_ttc_avant_remise_global_ttc',
            'numeroBonCommande' => 'numero_bon_commande',
            'numeroFacture' => 'numero_facture',
            'pieceJointeId' => 'piece_jointe_id',
            'typeFacture' => 'type_facture',
            'typeTva' => 'type_tva'
        ];

        foreach ($info_from_cpp as $item_chorus => $item_pastell) {
            if (array_key_exists($item_chorus, $result)) {
                $this->getDonneesFormulaire()->setData($item_pastell, $result[$item_chorus]);
            }
        }

        if (array_key_exists('numeroFacture', $result)) {
            $this->getDocument()->setTitre($this->id_d, $result['numeroFacture']);
        }

        if ((array_key_exists('montantAPayer', $result)) && (empty($result['montant_ttc_avant_remise_global_ttc']))) {
            $this->getDonneesFormulaire()->setData('montant_ttc_avant_remise_global_ttc', $result['montantAPayer']);
        }

        /* Le cadre de facturation par défaut est fixé à A1 (facture fournisseur) */
        $this->getDonneesFormulaire()->setData('cadre_facturation', 'A1_FACTURE_FOURNISSEUR');

        $message = "Le fichier PDF a été déposé. Chorus Pro lui a attribué l'identifiant {$result['pieceJointeId']}";

        $connecteurConfig = $this->getConnecteurConfigByType('PortailFacture');

        $id_structure_from_connecteur = $connecteurConfig->get('identifiant_structure');
        if (array_key_exists('codeFournisseur', $result) && ($id_structure_from_connecteur && $id_structure_from_connecteur != $result['codeFournisseur'])) {
            $this->getDonneesFormulaire()->setData('code_fournisseur', $id_structure_from_connecteur);
            $message .= "<br>L'identifiant structure {$result['codeFournisseur']} ne correspond pas à celui du connecteur !";
        }
        $this->getDonneesFormulaire()->setData('id_cpp_fournisseur', $connecteurConfig->get('identifiant_structure_cpp'));
        $this->getDonneesFormulaire()->setData('id_cpp_service_fournisseur', $connecteurConfig->get('service_destinataire'));


        $this->addActionOK($message);
        $this->redirect("/Document/edition?id_d={$this->id_d}&id_e={$this->id_e}&page=2");
        return true;
    }
}
