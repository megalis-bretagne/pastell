<?php

class SAEEnvoiActes extends ActionExecutor
{
    public function go()
    {
        /** @var TmpFolder $tmpFolder */
        $tmpFolder = $this->objectInstancier->getInstance(TmpFolder::class);
        $tmp_folder = $tmpFolder->create();
        try {
            $result = $this->goThrow($tmp_folder);
        } catch (Exception $e) {
            $tmpFolder->delete($tmp_folder);
            throw $e;
        }
        $tmpFolder->delete($tmp_folder);

        return $result;
    }

    private function goThrow($tmp_folder)
    {
        $donneesFormulaire = $this->getDonneesFormulaire();

        $arrete = $donneesFormulaire->copyFile('arrete', $tmp_folder, 0, "acte");
        $annexe = $donneesFormulaire->copyAllFiles('autre_document_attache', $tmp_folder, "annexe");
        $ar_actes = $donneesFormulaire->copyFile('aractes', $tmp_folder, 0, "aractes");
        $acte_tamponne = $donneesFormulaire->copyFile('acte_tamponne', $tmp_folder, 0, "acte_tamponne");
        $bdx_s2low = $donneesFormulaire->copyFile('bordereau', $tmp_folder, 0, "bordereau_s2low");

        $acte_nature = $this->getFormulaire()->getField('acte_nature')->getSelect();

        @ unlink($tmp_folder . "/empty");

        $transactionsInfo = [
            'numero_acte_collectivite' => $donneesFormulaire->get('numero_de_lacte'),
            'subject' => $donneesFormulaire->get('objet'),
            'decision_date' =>  $donneesFormulaire->get("date_de_lacte"),
            'latest_date' => $donneesFormulaire->get("date_de_lacte"),
            'nature_descr' => $acte_nature[$donneesFormulaire->get('acte_nature')],
            'nature_code' => $donneesFormulaire->get('acte_nature'),
            'classification' => $donneesFormulaire->get('classification'),
            'actes_file' => $arrete,
            'ar_actes' => $ar_actes,
            'annexe' => $annexe,
            'actes_file_orginal_filename' => $donneesFormulaire->getFileName('arrete', 0),
            'annexe_original_filename' => $donneesFormulaire->get('autre_document_attache'),
            'actes_file_tamponne' => $acte_tamponne,
            'bordereau_acquit' => $bdx_s2low
        ];

        if ($this->getDonneesFormulaire()->get('has_information_complementaire')) {
            $echangePrefecture = $this->getEchangePrefecture($donneesFormulaire, $tmp_folder);
        } else {
            $echangePrefecture = $this->getFromDocument($donneesFormulaire, $tmp_folder);
        }


        $transactionsInfo = array_merge($transactionsInfo, $echangePrefecture);

        if ($donneesFormulaire->get("signature")) {
            $transactionsInfo['signature'] = $donneesFormulaire->copyAllFiles('signature', $tmp_folder, "signature");
        }

        /** @var SEDAConnecteur $actesSEDA */
        $actesSEDA = $this->getConnecteur('Bordereau SEDA');

        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');


        if ($actesSEDA instanceof SedaNG) {
            /** @var SedaNG $actesSEDA */
            $fluxData = new FluxDataSedaActes($donneesFormulaire);
            $bordereau = $actesSEDA->getBordereauNG($fluxData);
            $donneesFormulaire->addFileFromData('sae_bordereau', "bordereau.xml", $bordereau);
            $transferId = $sae->getTransferId($bordereau);
            $donneesFormulaire->setData("sae_transfert_id", $transferId);

            try {
                $actesSEDA->validateBordereau($bordereau);
            } catch (Exception $e) {
                $message = $e->getMessage() . " : <br/><br/>";
                foreach ($actesSEDA->getLastValidationError() as $erreur) {
                    $message .= $erreur->message . "<br/>";
                }
                throw new Exception($message);
            }

            $archive_path = $tmp_folder . "/archive.tar.gz";
            // ! generateArchive doit être postérieur à getBordereauNG afin que la liste des fichiers à traiter (file_list de FluxDataSedaDefault) soit renseignée.
            $actesSEDA->generateArchive($fluxData, $archive_path);

            $donneesFormulaire->addFileFromCopy('sae_archive', "archive.tar.gz", $archive_path);
        } else {
            $bordereau = $actesSEDA->getBordereau($transactionsInfo);
            if (!$bordereau) {
                throw new Exception("Le bordereau n'a pas pu être généré : " . $actesSEDA->getLastError());
            }
            $transferId = $sae->getTransferId($bordereau);
            if (! $transferId) {
                throw new Exception("Impossible de récupérer le TransferId à partir du bordereau");
            }
            $donneesFormulaire->setData("sae_transfert_id", $transferId);
            $donneesFormulaire->addFileFromData('sae_bordereau', "bordereau.xml", $bordereau);
            $archive_path = $sae->generateArchive($bordereau, $tmp_folder);
            $donneesFormulaire->addFileFromCopy('sae_archive', "archive.tar.gz", $archive_path);
        }

        $result = $sae->sendArchive($bordereau, $archive_path);

        if (! $result) {
            $this->setLastMessage("L'envoi du bordereau a échoué : " . $sae->getLastError());
            return false;
        }

        $this->addActionOK("Le document a été envoyé au SAE");
        $this->notify($this->action, $this->type, "Le document a été envoyé au SAE");

        return true;
    }


    public function getEchangePrefecture(DonneesFormulaire $donneesFormulaire, $tmp_folder)
    {
        $result['echange_prefecture'] = $donneesFormulaire->copyAllFiles('echange_prefecture', $tmp_folder, "document-prefecture");
        $result['echange_prefecture_ar'] = [];
        $result['echange_prefecture_type'] = [];
        if ($donneesFormulaire->get("echange_prefecture_ar")) {
            foreach ($donneesFormulaire->get("echange_prefecture_ar") as $i => $ar_name) {
                if ($ar_name == "empty") {
                    $result['echange_prefecture_ar'][$i] = "empty";
                } else {
                    $result['echange_prefecture_ar'][$i] = $donneesFormulaire->copyFile('echange_prefecture_ar', $tmp_folder, $i, "ar-prefecture-$i");
                }
            }
        }


        foreach ($result['echange_prefecture'] as $i => $echange) {
            $result['echange_prefecture_type'][$i] = $donneesFormulaire->get("echange_prefecture_type_$i");
        }
        $result['echange_prefecture_original_filename'] = $donneesFormulaire->get('echange_prefecture');
        return $result;
    }


    public function getFromDocument(DonneesFormulaire $donneesFormulaire, $tmp_folder)
    {
        $nb_document  = 1;
        $result['echange_prefecture'] = [];
        $result['echange_prefecture_ar'] = [];
        $result['echange_prefecture_type'] = [];
        $result['echange_prefecture_original_filename'] = [];

        if ($donneesFormulaire->get('has_courrier_simple')) {
            $result['echange_prefecture'][] = $donneesFormulaire->copyFile('courrier_simple', $tmp_folder, 0, "document-prefecture-" . $nb_document++);
            $result['echange_prefecture_ar'][] = "empty";
            $result['echange_prefecture_type'][] = "2A";
            $result['echange_prefecture_original_filename'][] = $donneesFormulaire->getFileName('courrier_simple', 0);
        }

        if ($donneesFormulaire->get('has_demande_piece_complementaire')) {
            $result['echange_prefecture'][] = $donneesFormulaire->copyFile('demande_piece_complementaire', $tmp_folder, 0, "document-prefecture-" . $nb_document++);
            $result['echange_prefecture_ar'][] = "empty";
            $result['echange_prefecture_type'][] = "3A";
            $result['echange_prefecture_original_filename'][] = $donneesFormulaire->getFileName('demande_piece_complementaire', 0);
        }

        if ($donneesFormulaire->get('has_reponse_demande_piece_complementaire')) {
            $result['echange_prefecture'][] = $donneesFormulaire->copyFile('reponse_demande_piece_complementaire', $tmp_folder, 0, "document-prefecture-" . $nb_document++);
            $result['echange_prefecture_ar'][] = "empty";
            $result['echange_prefecture_type'][] = "3R";
            $result['echange_prefecture_original_filename'][] = $donneesFormulaire->getFileName('reponse_demande_piece_complementaire', 0);
            if ($donneesFormulaire->get('reponse_pj_demande_piece_complementaire')) {
                foreach ($donneesFormulaire->get('reponse_pj_demande_piece_complementaire') as $i => $filename) {
                    $result['echange_prefecture'][] = $donneesFormulaire->copyFile('reponse_pj_demande_piece_complementaire', $tmp_folder, $i, "document-prefecture-" . $nb_document++);
                    $result['echange_prefecture_ar'][] = "empty";
                    $result['echange_prefecture_type'][] = "3RB";
                    $result['echange_prefecture_original_filename'][] = $donneesFormulaire->getFileName('reponse_pj_demande_piece_complementaire', $i);
                }
            }
        }
        if ($donneesFormulaire->get('has_lettre_observation')) {
            $result['echange_prefecture'][] = $donneesFormulaire->copyFile('lettre_observation', $tmp_folder, 0, "document-prefecture-" . $nb_document++);
            $result['echange_prefecture_ar'][] = "empty";
            $result['echange_prefecture_type'][] = "4A";
            $result['echange_prefecture_original_filename'][] = $donneesFormulaire->getFileName('lettre_observation', 0);
        }

        if ($donneesFormulaire->get('has_reponse_lettre_observation')) {
            $result['echange_prefecture'][] = $donneesFormulaire->copyFile('reponse_lettre_observation', $tmp_folder, 0, "document-prefecture-" . $nb_document++);
            $result['echange_prefecture_ar'][] = "empty";
            $result['echange_prefecture_type'][] = "4R";
            $result['echange_prefecture_original_filename'][] = $donneesFormulaire->getFileName('reponse_lettre_observation', 0);
        }

        if ($donneesFormulaire->get('has_defere_tribunal_administratif')) {
            $result['echange_prefecture'][] = $donneesFormulaire->copyFile('defere_tribunal_administratif', $tmp_folder, 0, "document-prefecture-" . $nb_document++);
            $result['echange_prefecture_ar'][] = "empty";
            $result['echange_prefecture_type'][] = "5A";
            $result['echange_prefecture_original_filename'][] = $donneesFormulaire->getFileName('defere_tribunal_administratif', 0);
        }

        return $result;
    }
}
