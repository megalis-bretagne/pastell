<?php

declare(strict_types=1);

namespace Pastell\Connector\Ensap;

use DateTime;
use DOMDocument;
use Exception;
use InvalidArgumentException;
use SimpleXMLElement;

use function in_array;

class XmlEnveloppeValidator
{
    private const XSD_SCHEMA_PATH = __DIR__ . '/xsd/enveloppe_ENSAP_BPG_V1.3.2.xsd';
    private array $errors = [];

    private function addError(string $errorMessage, array $info = []): void
    {
        $errorMessage = $info ? $errorMessage . ' (' : $errorMessage;
        foreach ($info as $key => $value) {
            $errorMessage .= $key . ' : ' . $value . ', ';
        }
        $errorMessage .= $info ? ')' : '';
        $this->errors[] = $errorMessage;
    }

    public function validateXsd(string $xml): bool
    {
        $xsd = file_get_contents(self::XSD_SCHEMA_PATH);
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $dom->schemaValidateSource($xsd);
        return true;
    }

    /**
     * @throws Exception
     */
    public function validateContent(string $xml): bool
    {
        $this->errors = [];
        $xml_enveloppe = new SimpleXMLElement($xml);
        $this->validateEnveloppe($xml_enveloppe);
        if (!empty($this->errors)) {
            throw new InvalidArgumentException(implode(',<br>', $this->errors));
        }
        return true;
    }

    public function validateEnveloppe(SimpleXMLElement $xml_enveloppe): void
    {
        $this->validateMessage($xml_enveloppe->message);
        $this->validateEmetteur($xml_enveloppe->emetteur);
        foreach ($xml_enveloppe->assure as $xml_assure) {
            $this->validateAssure($xml_assure);
        }
    }

    public function validateMessage(SimpleXMLElement $xml_message): void
    {
        $date_traitement = (string)$xml_message->date_traitement[0];
        $date = DateTime::createFromFormat('dmY', $date_traitement);
        if (!$date) {
            $this->addError('La date doit être au format "JJMMAAAA"');
        }
    }

    public function validateEmetteur(SimpleXMLElement $xml_emetteur): void
    {
        $code_emetteur = (string)$xml_emetteur->code_emetteur;
        $length = mb_strlen($code_emetteur);
        if ($length !== 9) {
            $this->addError('Le code émetteur doit contenir exactement 9 caractères');
        }
    }

    public function validateAssure(SimpleXMLElement $xml_assure): void
    {
        $id_assure = (string)$xml_assure->numero_dossier;
        $error_info = ['assure' => $id_assure];

        $sexe = (string)$xml_assure->sexe;
        if (empty($sexe) || !in_array($sexe, ['1', '2'], true)) {
            $this->addError('Sexe invalide', $error_info);
        }

        $date_naissance = (string)$xml_assure->date_naissance;
        $date = DateTime::createFromFormat('dmY', $date_naissance);
        if ($date === false || $date->format('dmY') !== $date_naissance) {
            $this->addError('Date de naissance invalide', $error_info);
        }

        $iban = (string)$xml_assure->iban;
        if (!preg_match('/^[A-Za-z]{2}[0-9A-Z]*$/', $iban)) {
            $this->addError('L\'IBAN doit commencer par deux lettres suivies de 32 chiffres', $error_info);
        }

        $statut = (string)$xml_assure->statut;
        if (empty($statut) || !in_array($statut, ['T', 'C'], true)) {
            $this->addError('Le statut doit être C (Contractuel), T (Titulaire) ou vide', $error_info);
        }

        foreach ($xml_assure->gestionnaire as $xml_gestionnaire) {
            $this->validateGestionnaire($xml_gestionnaire, $id_assure);
        }
    }

    public function validateGestionnaire(SimpleXMLElement $xml_gestionnaire, string $id_assure): void
    {
        $id_gestionnaire = (string)$xml_gestionnaire->siret;
        $error_info = ['assure' => $id_assure, 'gestionnaire' => $id_gestionnaire];
        $siret = (string)$xml_gestionnaire->siret;
        if (mb_strlen($siret) !== 14 || !ctype_digit($siret)) {
            $this->addError('Le SIRET doit contenir exactement 14 chiffres', $error_info);
        }

        foreach ($xml_gestionnaire->document as $xml_document) {
            $this->validateDocument($xml_document, $id_assure, $id_gestionnaire);
        }
    }

    public function validateDocument(SimpleXMLElement $xml_document, string $id_assure, string $id_gestionnaire): void
    {
        $id_document = (string)$xml_document->nom_fichier;
        $error_info = ['assure' => $id_assure, 'gestionnaire' => $id_gestionnaire, 'document' => $id_document];
        $date_document = (string)$xml_document->date_document;
        $date = DateTime::createFromFormat('dmY', $date_document);
        if ($date === false || $date->format('d') !== '01') {
            $this->addError(
                'La date du document doit être au format "JJMMAAAA" et le jour doit être le premier du mois',
                $error_info
            );
        }

        $nom_fichier = (string)$xml_document->nom_fichier;
        $pattern = '/^\d{13,15}_\d{14}_BPaie_01\d{2}\d{4}\.pdf$/';
        if (!preg_match($pattern, $nom_fichier)) {
            $this->addError(
                'Le nom du fichier doit être au format "<NIR>_<SIRET>_BPaie_<DATE_DOCUMENT>.pdf"',
                $error_info
            );
        }
    }
}
