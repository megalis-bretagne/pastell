<?php

class FakeIparapheur extends SignatureConnecteur
{
    private $retour;
    private $iparapheur_type;
    /** @var string $iparapheur_sous_type */
    private $iparapheur_sous_type;
    private $iparapheur_envoi_status;
    private $iparapheur_temps_reponse;
    /** @var string $signatureField */
    private $signatureField;
    /** @var string $bordereauField */
    private $bordereauField;
    private $is_fast;

    public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties)
    {
        $this->retour = $collectiviteProperties->get('iparapheur_retour');
        $this->iparapheur_type = $collectiviteProperties->get('iparapheur_type');
        $this->iparapheur_sous_type = $collectiviteProperties->get('iparapheur_sous_type');
        $this->iparapheur_envoi_status = $collectiviteProperties->get('iparapheur_envoi_status');
        $this->iparapheur_temps_reponse = (int)$collectiviteProperties->get('iparapheur_temps_reponse');
        $this->signatureField = $collectiviteProperties->get('field_signature', 'signature');
        $this->bordereauField = $collectiviteProperties->get('field_bordereau', 'document_signe');
        $this->is_fast = $collectiviteProperties->get('is_fast', false);
    }

    public function getNbJourMaxInConnecteur()
    {
        return self::PARAPHEUR_NB_JOUR_MAX_DEFAULT;
    }

    public function getSousType()
    {
        switch ($this->iparapheur_type) {
            case 'Actes':
                return ["Arrêté individuel", "Arrêté réglementaire", "Contrat et convention", "Délibération"];
            case 'PES':
                return ["BJ", "Bordereau depense"];
            case 'Document':
                return ["Courrier", "Commande", "Facture"];
            case 'Custom':
                return explode(';', $this->iparapheur_sous_type);
            default:
                return [];
        }
    }

    public function getDossierID($id, $name)
    {
        $name = preg_replace("#[^a-zA-Z0-9_ ]#", "_", $name);
        return "$id $name";
    }

    /**
     * @param FileToSign $dossier
     * @return string
     * @throws Exception
     */
    public function sendDossier(FileToSign $dossier)
    {
        if ($this->iparapheur_envoi_status == 'error') {
            throw new Exception("Erreur déclenchée par le connecteur fake Iparapheur (iparapheur_envoi_status configuré à 'error')");
        }
        return "Dossier déposé pour signature";
    }

    /**
     * @deprecated 3.0
     */
    public function sendDocument(
        $typeTechnique,
        $sousType,
        $dossierID,
        $document_content,
        $content_type,
        array $all_annexes = [],
        $date_limite = false,
        $visuel_pdf = ''
    ) {
        if ($this->iparapheur_envoi_status == 'error') {
            throw new Exception("Erreur déclenchée par le connecteur fake Iparapheur (iparapheur_envoi_status configuré à 'error')");
        }
        return "Dossier déposé pour signature";
    }

    public function getHistorique($dossierID)
    {
        if ($this->retour == 'Fatal') {
            trigger_error("Fatal error", E_USER_ERROR);
        }
        sleep($this->iparapheur_temps_reponse);
        $date = date("d/m/Y H:i:s");
        if ($this->retour == 'Archive') {
            return $date . " : [Archive] Dossier signé (simulation de parapheur)!";
        }
        if ($this->retour == 'Rejet') {
            return $date . " : [RejetVisa] Dossier rejeté (simulation parapheur)!";
        }

        throw new Exception("Erreur provoquée par le simulateur du iParapheur");
    }

    public function getSignature($dossierID, $archive = true)
    {
        $info['document'] = "Document";
        $info['nom_document'] = "document.txt";
        $info['is_pes'] = false;

        $document = $this->getDocDonneesFormulaire();
        if ($document->get($this->signatureField)) {
            $info['document_signe'] = [
                'document' => $document->getFileContent($this->signatureField),
                'nom_document' => $document->getFileName($this->signatureField)
            ];
        } else {
            $info['signature'] = "Test Signature";
            $info['document_signe'] = [
                'document' => "content",
                'nom_document' => "document_signe.txt"
            ];
        }
        if ($document->get($this->bordereauField)) {
            $info['nom_document'] = $document->getFileName($this->bordereauField);
            $info['document'] = $document->getFileContent($this->bordereauField);
        }

        return $info;
    }

    public function sendHeliosDocument(
        $typeTechnique,
        $sousType,
        $dossierID,
        $document_content,
        $content_type,
        $visuel_pdf,
        array $metadata = []
    ) {
        return true;
    }

    public function getAllHistoriqueInfo($dossierID)
    {
        if ($this->retour == 'Fatal') {
            trigger_error("Fatal error", E_USER_ERROR);
        }
        sleep($this->iparapheur_temps_reponse);
        $date = date("d/m/Y H:i:s");
        if ($this->retour == 'Archive') {
            return [$date . " : [Archive] Dossier signé (simulation de parapheur)!"];
        }
        if ($this->retour == 'Rejet') {
            return [$date . " : [RejetVisa] Dossier rejeté (simulation parapheur)!"];
        }

        throw new Exception("Erreur provoquée par le simulateur du iParapheur");
    }

    public function getLastHistorique($dossierID)
    {

        if ($this->retour == 'Archive') {
            return "[Archive]";
        }
        return "[RejetVisa]";
    }

    public function effacerDossierRejete($dossierID)
    {
        return true;
    }

    public function getLogin()
    {
        return "ok";
    }

    public function isFinalState(string $lastState): bool
    {
        return strstr($lastState, '[Archive]');
    }

    public function isRejected(string $lastState): bool
    {
        return strstr($lastState, '[RejetVisa]') || strstr($lastState, '[RejetSignataire]');
    }

    public function isDetached($signature): bool
    {
        return $signature['signature'] && !$signature['is_pes'];
    }

    /**
     * Workaround because IParapheur::getSignature() does not return only the signature
     *
     * @param $file
     * @return mixed
     */
    public function getDetachedSignature($file)
    {
        return $file['signature'];
    }

    /**
     * Workaround because IParapheur::getSignature() does not return only the signature
     *
     * @param $file
     * @return mixed
     */
    public function getSignedFile($file)
    {
        return $file['signature'] ?: $file['document_signe']['document'];
    }


    /**
     * Workaround because it is embedded in IParapheur::getSignature()
     *
     * @param $signature
     * @return Fichier
     */
    public function getBordereauFromSignature($signature): ?Fichier
    {
        $file = new Fichier();
        $file->filename = $signature['nom_document'];
        $file->content = $signature['document'];
        return $file;
    }

    public function isFastSignature()
    {
        return $this->is_fast;
    }

    /**
     * @param $dossierID
     * @return bool
     */
    public function exercerDroitRemordDossier($dossierID): bool
    {
        return true;
    }
}
