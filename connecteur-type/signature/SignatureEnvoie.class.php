<?php

require_once PASTELL_PATH . DIRECTORY_SEPARATOR . 'pastell-core' . DIRECTORY_SEPARATOR . 'FileToSign.php';

class SignatureEnvoie extends ConnecteurTypeActionExecutor
{

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var SignatureConnecteur $signature */
        $signature = $this->getConnecteur('signature');

        $donneesFormulaire = $this->getDonneesFormulaire();

        $document_element = $this->getMappingValue('document');
        $objet_element = $this->getMappingValue('objet');
        $visuel_pdf_element = $this->getMappingValue('visuel_pdf');
        $iparapheur_type_element = $this->getMappingValue('iparapheur_type');
        $iparapheur_sous_type_element = $this->getMappingValue('iparapheur_sous_type');
        $fast_parapheur_circuit = $this->getMappingValue('fast_parapheur_circuit');
        $fast_parapheur_circuit_configuration = $this->getMappingValue('fast_parapheur_circuit_configuration');
        $has_date_limite = $this->getMappingValue('iparapheur_has_date_limite');
        $iparapheur_date_limite = $this->getMappingValue('iparapheur_date_limite');
        $annexe_element = $this->getMappingValue('autre_document_attache');
        $primo_signature_detachee = $this->getMappingValue('primo_signature_detachee');
        $json_metadata = $this->getMappingValue('json_metadata');
        $iparapheur_dossier_id = $this->getMappingValue('iparapheur_dossier_id');

        $fileToSign = new FileToSign();
        $fileToSign->type = $donneesFormulaire->get($iparapheur_type_element);
        $fileToSign->sousType = $donneesFormulaire->get($iparapheur_sous_type_element);
        $fileToSign->circuit = $donneesFormulaire->get($fast_parapheur_circuit);

        $fileToSign->circuit_configuration = new Fichier();
        if ($donneesFormulaire->get($fast_parapheur_circuit_configuration)) {
            $fileToSign->circuit_configuration->filename =
                $donneesFormulaire->getFileName($fast_parapheur_circuit_configuration);
            $fileToSign->circuit_configuration->filepath =
                $donneesFormulaire->getFilePath($fast_parapheur_circuit_configuration);
            $fileToSign->circuit_configuration->content =
                $donneesFormulaire->getFileContent($fast_parapheur_circuit_configuration);
            $fileToSign->circuit_configuration->contentType =
                $donneesFormulaire->getContentType($fast_parapheur_circuit_configuration);
        }

        $fileToSign->document = new Fichier();
        $fileToSign->document->filename = $donneesFormulaire->getFileName($document_element);
        $fileToSign->document->filepath = $donneesFormulaire->getFilePath($document_element);
        $fileToSign->document->content = $donneesFormulaire->getFileContent($document_element);
        $fileToSign->document->contentType = $donneesFormulaire->getContentType($document_element);

        $fileToSign->visualPdf = new Fichier();
        if ($donneesFormulaire->get($visuel_pdf_element)) {
            $fileToSign->visualPdf->filename = $donneesFormulaire->getFileName($visuel_pdf_element);
            $fileToSign->visualPdf->filepath = $donneesFormulaire->getFilePath($visuel_pdf_element);
            $fileToSign->visualPdf->content = $donneesFormulaire->getFileContent($visuel_pdf_element);
            $fileToSign->visualPdf->contentType = $donneesFormulaire->getContentType($visuel_pdf_element);
        }

        if ($donneesFormulaire->get($annexe_element)) {
            foreach ($donneesFormulaire->get($annexe_element) as $num => $fileName) {
                $annexe = new Fichier();
                $annexe->filename = $donneesFormulaire->getFileName($annexe_element, $num);
                $annexe->filepath = $donneesFormulaire->getFilePath($annexe_element, $num);
                $annexe->content = $donneesFormulaire->getFileContent($annexe_element, $num);
                $annexe->contentType = $donneesFormulaire->getContentType($annexe_element, $num);

                $fileToSign->annexes[] = $annexe;
            }
        }

        if ($donneesFormulaire->get($primo_signature_detachee)) {
            $fileToSign->signature_content = $donneesFormulaire->getFileContent($primo_signature_detachee);
            $fileToSign->signature_type = $donneesFormulaire->getContentType($primo_signature_detachee);
            if ($fileToSign->signature_type != 'application/xml') {
                $fileToSign->signature_type = 'application/pkcs7-signature';
            }
        }

        $fileToSign->dossierTitre = $donneesFormulaire->get($objet_element);

        $fileToSign->metadata = json_decode(
            $donneesFormulaire->getFileContent($json_metadata),
            true
        );


        if ($donneesFormulaire->getFormulaire()->getField($iparapheur_dossier_id)) {
            $fileToSign->dossierId = date("YmdHis") . mt_rand(0, mt_getrandmax());
        } else { // conservé pour compatibilité
            $fileToSign->dossierId = $signature->getDossierID(
                $donneesFormulaire->get($objet_element),
                $fileToSign->document->filename
            );
        }

        $signature->setSendingMetadata($donneesFormulaire);

        if ($donneesFormulaire->get($has_date_limite)) {
            $fileToSign->date_limite = $donneesFormulaire->get($iparapheur_date_limite);
        }

        $result = $signature->sendDossier($fileToSign);
        if (!$result) {
            $this->setLastMessage("La connexion avec le parapheur a échoué : " . $signature->getLastError());
            return false;
        }

        $donneesFormulaire->setData($iparapheur_dossier_id, $result);
        $this->addActionOK("Le document a été envoyé au parapheur électronique");
        $this->notify($this->action, $this->type, "Le document a été envoyé au parapheur électronique");
        return true;
    }
}
