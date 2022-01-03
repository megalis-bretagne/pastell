<?php

class ExtraireDonneesPivot
{
    protected $objectInstancier;

    public function __construct(ObjectInstancier $objectInstancier)
    {
        $this->objectInstancier = $objectInstancier;
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return array|string
     */
    public function getFournisseur(DonneesFormulaire $donneesFormulaire)
    {
        $filePath = $donneesFormulaire->getFilePath('fichier_facture');
        if (! $filePath) {
            return "Il n'y a pas de fichier pivot";
        }
        $xml = simplexml_load_file($filePath, "SimpleXMLElement", LIBXML_PARSEHUGE);

        if (empty($xml->{'CPPFactures'}->{'CPPFacturePivotUnitaire'}->{'Fournisseur'})) {
            return "Il n'y a pas de données Fournisseur";
        }
        $fournisseur = $xml->{'CPPFactures'}->{'CPPFacturePivotUnitaire'}->{'Fournisseur'};

        return array(
            'fournisseur_type_id' => strval($fournisseur->TypeIdentifiant), // ref flux pivot
            'type_identifiant'  => strval($fournisseur->TypeIdentifiant), // ref flux cpp
            'fournisseur' => strval($fournisseur->Identifiant),
            'fournisseur_raison_sociale' => strval($fournisseur->RaisonSociale),
            'fournisseur_code_pays' => strval($fournisseur->CodePays),
            'fournisseur_ref_bancaire_type' => strval($fournisseur->ReferenceBancaire['Type']),
            'fournisseur_ref_bancaire_compte' => strval($fournisseur->ReferenceBancaire->Compte),
            'fournisseur_ref_bancaire_etablissement' => strval($fournisseur->ReferenceBancaire->Etablissement),
            'fournisseur_mode_emission' => strval($fournisseur->ModeEmission),
        );
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return array|string
     */
    public function getDebiteur(DonneesFormulaire $donneesFormulaire)
    {
        $filePath = $donneesFormulaire->getFilePath('fichier_facture');
        if (! $filePath) {
            return "Il n'y a pas de fichier pivot";
        }
        $xml = simplexml_load_file($filePath, "SimpleXMLElement", LIBXML_PARSEHUGE);

        if (empty($xml->{'CPPFactures'}->{'CPPFacturePivotUnitaire'}->{'Debiteur'})) {
            return "Il n'y a pas de données Debiteur";
        }
        $debiteur = $xml->{'CPPFactures'}->{'CPPFacturePivotUnitaire'}->{'Debiteur'};

        return array(
            'siret' => strval($debiteur->Identifiant),
            'destinataire_nom' => strval($debiteur->Nom),
            'service_destinataire_code' => strval($debiteur->CodeService),
        );
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return array|string
     */
    public function getDonneesFacture(DonneesFormulaire $donneesFormulaire)
    {
        $filePath = $donneesFormulaire->getFilePath('fichier_facture');
        if (! $filePath) {
            return "Il n'y a pas de fichier pivot";
        }
        $xml = simplexml_load_file($filePath, "SimpleXMLElement", LIBXML_PARSEHUGE);

        if (empty($xml->{'CPPFactures'}->{'CPPFacturePivotUnitaire'}->{'DonneesFacture'})) {
            return "Il n'y a pas de données Facture";
        }
        $donnees_facture = $xml->{'CPPFactures'}->{'CPPFacturePivotUnitaire'}->{'DonneesFacture'};

        return array(
            'no_facture' => strval($donnees_facture->Id),
            'facture_type' => strval($donnees_facture->Type), // ref flux pivot
            'type_facture' => strval($donnees_facture->Type), // ref flux cpp
            'facture_cadre' => strval($donnees_facture->Cadre),
            'date_facture' => strval($donnees_facture->DateEmissionFacture),
            'facture_date_reception' => strval($donnees_facture->DateReception),
            'facture_mode_paiement_code' => strval($donnees_facture->ModePaiement->Code),
            'facture_mode_paiement_libelle' => strval($donnees_facture->ModePaiement->Libelle),
            'facture_devise' => strval($donnees_facture->Devise),
            'facture_montant_ht' => strval($donnees_facture->Montants->MontantHT),
            'montant_ttc' => strval($donnees_facture->Montants->MontantTTC),
            'facture_montant_net' => strval($donnees_facture->Montants->MontantNetAPayer),
            'facture_numero_marche' => strval($donnees_facture->Engagement->NumeroMarche),
            'facture_numero_engagement' => strval($donnees_facture->Engagement->NumeroEngagement),
        );
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return bool|string
     * @throws Exception
     */
    public function getAllPJ(DonneesFormulaire $donneesFormulaire)
    {
        $donneesFormulaire->deleteField('facture_pj_01');
        $donneesFormulaire->deleteField('facture_pj_02');
        $donneesFormulaire->deleteField('fichier_facture_pdf');
        $is_affect_pdf = false;

        $filePath = $donneesFormulaire->getFilePath('fichier_facture');
        if (! $filePath) {
            return "Il n'y a pas de fichier pivot";
        }
        $xml = simplexml_load_file($filePath, "SimpleXMLElement", LIBXML_PARSEHUGE);
        if (empty($xml->{'CPPFactures'}->{'CPPFacturePivotUnitaire'}->{'PJ'})) {
            return "Il n'y a pas de pièces jointes";
        }

        $file_num = 0;
        foreach ($xml->{'CPPFactures'}->{'CPPFacturePivotUnitaire'}->{'PJ'} as $pj) {
            if ($pj->{'TypePJ'} == '01') {
                $this->extrairePJ($donneesFormulaire, $pj, 'facture_pj_01', 0);
                if (!$is_affect_pdf) {
                    $is_affect_pdf = $this->affect_pdf($donneesFormulaire, 'facture_pj_01', 0);
                }
            }
            if ($pj->{'TypePJ'} == '02') {
                $this->extrairePJ($donneesFormulaire, $pj, 'facture_pj_02', $file_num);
                if (!$is_affect_pdf) {
                    $file_name = $donneesFormulaire->getFileName('facture_pj_02', $file_num);
                    $num_pj = str_split($file_name, 4);
                    if ($num_pj[0] == "PJ00") {
                        $is_affect_pdf = $this->affect_pdf($donneesFormulaire, 'facture_pj_02', $file_num);
                    }
                }
                $file_num++;
            }
        }
        return true;
    }

    /**
     * @param $donneesFormulaire
     * @param $pj
     * @param $field_name
     * @param $file_num
     * @return bool
     * @throws Exception
     */
    private function extrairePJ($donneesFormulaire, $pj, $field_name, $file_num)
    {
        $pj_nom = strval($pj->NomPJ);

        $pj_contenu = strval($pj->Contenu);
        $file_content = base64_decode($pj_contenu, true);
        /** @var DonneesFormulaire $donneesFormulaire */
        $donneesFormulaire->addFileFromData($field_name, $pj_nom, $file_content, $file_num);

        $content_type = $donneesFormulaire->getContentType($field_name, $file_num);

        if ($content_type == "application/zip") {
            $tmpFolder = $this->objectInstancier->getInstance(TmpFolder::class);
            $tmp_folder = $tmpFolder->create();
            $zip = $this->objectInstancier->getInstance(ZipArchive::class);
            $handle = $zip->open($donneesFormulaire->getFilePath($field_name, $file_num));
            if (!$handle) {
                throw new Exception("Impossible d'ouvrir le fichier zip");
            }
            $zip->extractTo($tmp_folder);
            $zip->close();

            $file_list = scandir($tmp_folder);
            foreach ($file_list as $file_result) {
                $file_result_path = $tmp_folder . "/" . $file_result;
                if (is_file($file_result_path)) {
                    $donneesFormulaire->addFileFromCopy($field_name, $pj_nom, $file_result_path, $file_num);
                }
            }
            $tmpFolder->delete($tmp_folder);
        }
        return true;
    }


    /**
     * @param $donneesFormulaire
     * @param $field_name
     * @param $file_num
     * @return bool
     * @throws DonneesFormulaireException
     */
    private function affect_pdf($donneesFormulaire, $field_name, $file_num)
    {
        /** @var DonneesFormulaire $donneesFormulaire */
        $content_type = $donneesFormulaire->getContentType($field_name, $file_num);

        if ($content_type == 'application/pdf') {
            $file_name = $donneesFormulaire->getFileName($field_name, $file_num);
            $file_path = $donneesFormulaire->getFilePath($field_name, $file_num);
            $donneesFormulaire->addFileFromCopy('fichier_facture_pdf', $file_name, $file_path, 0);
            return true;
        }
        return false;
    }
}
