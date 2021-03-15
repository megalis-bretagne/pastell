<?php

abstract class SignatureConnecteur extends Connecteur
{

    public const PARAPHEUR_NB_JOUR_MAX_DEFAULT = 30;

    abstract public function getNbJourMaxInConnecteur();

    abstract public function getSousType();

    abstract public function getDossierID($id, $name);

    /**
     * @throws SignatureException
     */
    abstract public function sendDossier(FileToSign $dossier);

    /**
     * @deprecated 3.0 Use sendDossier() instead.
     */
    abstract public function sendDocument($typeTechnique, $sousType, $dossierID, $document_content, $content_type, array $all_annexes = array(), $date_limite = false, $visuel_pdf = '');

    abstract public function getHistorique($dossierID);

    abstract public function getSignature($dossierID, $archive = true);

    abstract public function sendHeliosDocument($typeTechnique, $sousType, $dossierID, $document_content, $content_type, $visuel_pdf, array $metadata = array());

    abstract public function getAllHistoriqueInfo($dossierID);

    abstract public function getLastHistorique($dossierID);

    abstract public function effacerDossierRejete($dossierID);

    abstract public function exercerDroitRemordDossier($dossierID);

    public function hasTypeSousType()
    {
        return true;
    }

    /**
    * Indique si le connecteur est un connecteur de signature "locale", c'est à dire par applet sur le navigateur et sans appel à un serveur de signature externe
    * @return boolean
    */
    public function isLocalSignature()
    {
        return false;
    }

    public function isFastSignature()
    {
        return false;
    }

    public function setSendingMetadata(DonneesFormulaire $donneesFormulaire)
    {
/*Nothing to do*/
    }

    public function archiver($dossierID)
    {
        return true;
    }

    public function getOutputAnnexe($info_from_get_signature, int $ignore_count)
    {
        return [];
    }

    abstract public function isFinalState(string $lastState): bool;
    abstract public function isRejected(string $lastState): bool;
    abstract public function isDetached($signature): bool;

    /**
     * Workaround because IParapheur::getSignature() does not return only the signature
     *
     * @param $file
     * @return mixed
     */
    abstract public function getDetachedSignature($file);

    /**
     * Workaround because IParapheur::getSignature() does not return only the signature
     *
     * @param $file
     * @return mixed
     */
    abstract public function getSignedFile($file);

    /**
     * Workaround because it is embedded in IParapheur::getSignature()
     *
     * @param $signature
     * @return Fichier
     */
    abstract public function getBordereauFromSignature($signature): ?Fichier;

    public function hasBordereau()
    {
        return true;
    }
}
