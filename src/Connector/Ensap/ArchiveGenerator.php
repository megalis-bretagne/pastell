<?php

namespace Pastell\Connector\Ensap;

use DOMDocument;
use DOMException;
use DonneesFormulaire;
use Exception;
use InvalidArgumentException;
use Pastell\Connector\Ensap\builders\AssureBuilder;
use Pastell\Connector\Ensap\builders\DocumentBuilder;
use Pastell\Connector\Ensap\builders\EmetteurBuilder;
use Pastell\Connector\Ensap\builders\EnveloppeBuilder;
use Pastell\Connector\Ensap\builders\GestionnaireBuilder;
use Pastell\Connector\Ensap\builders\MessageBuilder;
use Pastell\Connector\Ensap\parts\Assure;
use Pastell\Connector\Ensap\parts\Document;
use Pastell\Connector\Ensap\parts\Enveloppe;
use Pastell\Connector\Ensap\parts\Gestionnaire;
use Phar;
use PharData;
use RuntimeException;
use TmpFolder;
use XSDValidator;

class ArchiveGenerator
{
    private const XSD_SCHEMA_PATH = __DIR__ . '/xsd/enveloppe_ENSAP_BPG_V1.3.xsd';

    public function __construct(
        private readonly EnveloppeBuilder $enveloppeBuilder,
        private readonly TmpFolder $tmpFolder,
        private readonly XSDValidator $xsdValidator,
    ) {
    }

    /**
     * @throws Exception
     */
    public function generateArchive(DonneesFormulaire $donneesFormulaire): string
    {
        try {
            $tmpFolder = $this->tmpFolder->create();
        } catch (Exception $e) {
            throw new RuntimeException('Cannot create temporary directory : ' . $e->getMessage());
        }

        $archiveName = $this->generateArchiveName($donneesFormulaire);
        $enveloppe = $this->generateEnveloppe($donneesFormulaire);
        $xml = $this->generateXML($enveloppe);
        $archiveName = $this->createTarGzArchive($archiveName, $donneesFormulaire->get('document'), $xml, $tmpFolder);
        return (new GPGEncryptor())->encrypt($archiveName, $tmpFolder);
    }

    public function generateArchiveName(
        DonneesFormulaire $donneesFormulaire,
    ): string {
        $archiveName = 'ENVOI-PJ-BPG-' .
            $donneesFormulaire->get('sstheme') . '-' .
            $donneesFormulaire->get('nom_emetteurSRE') . '-' .
            $donneesFormulaire->get('code_emetteurSRE') . '-' .
            date('Ym') . '-' .
            date('YmdHis');
        if (!preg_match('/^ENVOI-PJ-BPG-(43|45)-\w{1,5}-\w{1,5}-\d{6}-\d{14}$/', $archiveName)) {
            throw new InvalidArgumentException("Invalid archive name: $archiveName");
        }
        return $archiveName;
    }

    public function generateEnveloppe(DonneesFormulaire $donneesFormulaire): Enveloppe
    {
        $documentBuilder = new DocumentBuilder();
        $document = $documentBuilder->setDateDocument($donneesFormulaire->get('date_document'))
            ->setNomFichier($donneesFormulaire->get('titre_document'))
            ->build();

        $gestionnaireBuilder = new GestionnaireBuilder();
        $gestionnaire = $gestionnaireBuilder->setSiret($donneesFormulaire->get('siret_collectivite'))
            ->addDocument($document)
            ->build();

        $assureBuilder = new AssureBuilder();
        $assure = $assureBuilder->setNumeroDossier($donneesFormulaire->get('matricule_agent'))
            ->setNumeroOrdre('1')
            ->setNir('MANQUANT')
            ->setNomNaissance($donneesFormulaire->get('nom_naissance_agent'))
            ->setSexe('MANQUANT')
            ->setDateNaissance($donneesFormulaire->get('date_naissance_agent'))
            ->setStatut($donneesFormulaire->get('statut_agent'))
            ->setReferenceEmetteur($donneesFormulaire->get('matricule_agent'))
            ->addGestionnaire($gestionnaire)
            ->build();

        $emetteurBuilder = new EmetteurBuilder();
        $emetteur = $emetteurBuilder->setCodeEmetteur($donneesFormulaire->get('siret_collectivite'))
            ->build();

        $messageBuilder = new MessageBuilder();
        $message = $messageBuilder->setDateTraitement(date('dmY'))
            ->setNomFichier($this->generateArchiveName($donneesFormulaire))
            ->build();

        $enveloppeBuilder = new EnveloppeBuilder();
        return $enveloppeBuilder->setMessage($message)
            ->setEmetteur($emetteur)
            ->addAssure($assure)
            ->build();
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

    public function createTarGzArchive(
        string $archiveName,
        string $documentContent,
        string $xmlContent,
        string $tmpFolder
    ): string {
        $tar = new PharData("$tmpFolder/$archiveName.tar");
        $tar->addFromString('document.txt', $documentContent);
        $tar->addFromString('index.xml', $xmlContent);
        $tar->compress(Phar::GZ);
        unlink("$tmpFolder/$archiveName.tar");
        return "$archiveName.tar.gz";
    }
}
