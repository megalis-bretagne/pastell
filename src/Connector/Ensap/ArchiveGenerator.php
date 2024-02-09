<?php

namespace Pastell\Connector\Ensap;

use Pastell\Connector\Ensap\enveloppe\Assure;
use Pastell\Connector\Ensap\enveloppe\Document;
use Pastell\Connector\Ensap\enveloppe\Enveloppe;
use Pastell\Connector\Ensap\enveloppe\Gestionnaire;
use DateTime;
use DOMDocument;
use DOMException;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use SimpleXMLElement;
use TmpFolder;
use XSDValidator;
use ZipArchive;

class ArchiveGenerator
{
    private const XSD_SCHEMA_PATH = __DIR__ . '/xsd/enveloppe_ENSAP_BPG_V1.3.xsd';
    private array $enveloppeData;

    public function __construct(
        private readonly EnveloppeBuilder $enveloppeBuilder,
        private readonly TmpFolder $tmpFolder,
        private readonly XSDValidator $xsdValidator,
    ) {
    }

    public function setData(array $enveloppeData): void
    {
        $this->enveloppeData = $enveloppeData;
    }

    public function getEnveloppe(): Enveloppe
    {
        $this->enveloppeBuilder->setMessage($this->enveloppeData['message'])->setEmetteur($this->enveloppeData['emetteur']);
        foreach ($this->enveloppeData['assures'] as $assure) {
            $this->enveloppeBuilder->addAssure($assure);
        }
        return $this->enveloppeBuilder->build();
    }

    /**
     * @throws DOMException
     */
    public function generateXML($enveloppe): string
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $envoiBPGenerique = $dom->createElement('envoi-BP-Generique');

        $message = $dom->createElement('message');
        $message->appendChild($dom->createElement('version_fichier', $enveloppe->message->versionFichier));
        $message->appendChild($dom->createElement('nature_flux', $enveloppe->message->natureFlux));
        $message->appendChild($dom->createElement('nom_fichier', $enveloppe->message->nomFichier));
        $message->appendChild($dom->createElement('date_traitement', $enveloppe->message->dateTraitement));
        $envoiBPGenerique->appendChild($message);

        $emetteur = $dom->createElement('emetteur');
        $emetteur->appendChild($dom->createElement('code_emetteur', $enveloppe->emetteur->codeEmetteur));
        $emetteur->appendChild($dom->createElement('code_CFT', $enveloppe->emetteur->codeCFT));
        $envoiBPGenerique->appendChild($emetteur);

        /** @var Assure $assure */
        foreach ($enveloppe->assures as $assure) {
            $assureElement = $dom->createElement('assure');

            $assureElement->appendChild($dom->createElement('numero_dossier', $assure->numeroDossier));
            $assureElement->appendChild($dom->createElement('numero_ordre', $assure->numeroOrdre));
            $assureElement->appendChild($dom->createElement('nir', $assure->nir));
            $assureElement->appendChild($dom->createElement('nom_naissance', $assure->nomNaissance));

            if (isset($assure->sexe)) {
                $assureElement->appendChild($dom->createElement('sexe', $assure->sexe));
            }

            $assureElement->appendChild($dom->createElement('date_naissance', $assure->dateNaissance));
            $assureElement->appendChild($dom->createElement('iban', $assure->iban));
            $assureElement->appendChild($dom->createElement('statut', $assure->statut));

            if (isset($assure->referenceEmetteur)) {
                $assureElement->appendChild($dom->createElement('reference_emetteur', $assure->referenceEmetteur));
            }

            /** @var Gestionnaire $gestionnaire */
            foreach ($assure->gestionnaires as $gestionnaire) {
                $gestionnaireElement = $dom->createElement('gestionnaire');

                if (isset($gestionnaire->codeGestion)) {
                    $gestionnaireElement->appendChild($dom->createElement('code_gestion', $gestionnaire->codeGestion));
                }

                if (isset($gestionnaire->codePoste)) {
                    $gestionnaireElement->appendChild($dom->createElement('code_poste', $gestionnaire->codePoste));
                }

                $gestionnaireElement->appendChild($dom->createElement('siret', $gestionnaire->siret));

                /** @var Document $document */
                foreach ($gestionnaire->documents as $document) {
                    $documentElement = $dom->createElement('document');

                    $documentElement->appendChild($dom->createElement('theme', (string)$document->theme));
                    $documentElement->appendChild($dom->createElement('sstheme', (string)$document->sstheme));
                    $documentElement->appendChild($dom->createElement('date_document', $document->dateDocument));
                    if (isset($document->montant)) {
                        $documentElement->appendChild($dom->createElement('montant', $document->montant));
                    }
                    $documentElement->appendChild($dom->createElement('nom_fichier', $document->nomFichier));
                    $gestionnaireElement->appendChild($documentElement);
                }
                $assureElement->appendChild($gestionnaireElement);
            }
            $envoiBPGenerique->appendChild($assureElement);
        }
        $dom->appendChild($envoiBPGenerique);
        return $dom->saveXML();
    }
    public function validateXML(string $xml): bool
    {
        try {
            return $this->xsdValidator->schemaValidateFromContent(self::XSD_SCHEMA_PATH, $xml);
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws Exception
     */
    public function generateArchive(array $pdf_documents, string $xml, string $emitterName, string $emitterCode): string
    {
        $xmlElement = new SimpleXMLElement($xml);
        $subTheme = $xmlElement->assure->gestionnaire->document->sstheme ?? null;
        //$emitterCode = $xmlElement->emetteur->code_emetteur ?? null;
        $period = $xmlElement->assure->gestionnaire->document->date_document ?? null;
        $formatedDate = DateTime::createFromFormat('dmY', (string)$period)->format('Ym');
        $timestamp = date('YmdHis');

        $archiveName = "ENVOI-PJ-BPG-{$subTheme}-{$emitterName}-{$emitterCode}-{$formatedDate}-{$timestamp}.tar.gz.gpg";
        if (!preg_match('/^ENVOI-PJ-BPG-(43|45)-\w{1,5}-\w{1,5}-\d{6}-\d{14}.tar.gz.gpg$/', $archiveName)) {
            throw new InvalidArgumentException("Invalid archive name: $archiveName");
        }

        try {
            $zipDirectory = $this->tmpFolder->create();
        } catch (Exception $e) {
            throw new RuntimeException('Cannot create temporary directory : ' . $e->getMessage());
        }

        $zip = new ZipArchive();
        if ($zip->open("$zipDirectory/$archiveName", ZipArchive::CREATE) === true) {
            foreach ($pdf_documents as $name => $content) {
                $zip->addFromString($name, $content);
            }
            $zip->addFromString('document.xml', $xml);
            $zip->close();
        } else {
            throw new RuntimeException("Cannot open archive: $archiveName");
        }
        return $archiveName;
    }
}
