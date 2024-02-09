<?php

namespace Pastell\Tests\Connector;

use DOMException;
use Exception;
use Pastell\Connector\Ensap\ArchiveGenerator;
use Pastell\Connector\Ensap\enveloppe\Emetteur;
use Pastell\Connector\Ensap\enveloppe\Enveloppe;
use Pastell\Connector\Ensap\enveloppe\Message;
use Pastell\Connector\Ensap\EnveloppeBuilder;
use Pastell\Connector\Ensap\enveloppe\Assure;
use Pastell\Connector\Ensap\enveloppe\Document;
use Pastell\Connector\Ensap\enveloppe\Gestionnaire;
use PastellTestCase;
use XSDValidator;

class EnsapEnveloppeBuilderTest extends PastellTestCase
{
    private ArchiveGenerator $archiveGenerator;
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->archiveGenerator = $this->getObjectInstancier()->getInstance(ArchiveGenerator::class);
    }

    public function createEnveloppe(): Enveloppe
    {
        $document = new Document();
        $document->theme = 8;
        $document->sstheme = 43;
        $document->dateDocument = '01092021';
        $document->montant = '200055';
        $document->nomFichier = '1870194035088_13000920600010_BPaie_01092021.pdf';

        $gestionnaire = new Gestionnaire();
        $gestionnaire->siret = '13000920600010';
        $gestionnaire->documents = [$document];

        $assure = new Assure();
        $assure->numeroDossier = '12589';
        $assure->numeroOrdre = '2';
        $assure->nir = '1641275074060';
        $assure->nomNaissance = 'BERNARD';
        $assure->dateNaissance = '11121964';
        $assure->iban = 'IBAN à insérer';
        $assure->statut = 'T';
        $assure->gestionnaires = [$gestionnaire];

        $emetteur = new Emetteur();
        $emetteur->codeEmetteur = '13000920600022';
        $emetteur->codeCFT = '';

        $message = new Message();
        $message->versionFichier = '01.00';
        $message->natureFlux = 'ENVOI-BP-GENERIQUE';
        $message->nomFichier = 'ENVOI-PJ-BPG-43-XXXXX-PXXXX-202109-290921113858';
        $message->dateTraitement = '29092021';

        $this->archiveGenerator->setData(['emetteur' => $emetteur, 'assures' => [$assure], 'message' => $message]);
        return $this->archiveGenerator->getEnveloppe();
    }

    /**
     * @throws DOMException
     */
    public function testGenerateXML(): void
    {
        $enveloppe = $this->createEnveloppe();
        $xml = $this->archiveGenerator->generateXML($enveloppe);

        static::assertTrue(strpos($xml, '<theme>') < strpos($xml, '<sstheme>'));
        static::assertTrue(strpos($xml, '<sstheme>') < strpos($xml, '<date_document>'));
        static::assertTrue(strpos($xml, '<date_document>') < strpos($xml, '<montant>'));
        static::assertTrue(strpos($xml, '<id_piece>') < strpos($xml, '<nom_fichier>'));
    }

    /**
     * @throws DOMException
     */
    public function testValidateXML(): void
    {
        $enveloppe = $this->createEnveloppe();
        $xml = $this->archiveGenerator->generateXML($enveloppe);
        static::assertTrue($this->archiveGenerator->validateXML($xml));
    }

    /**
     * @throws DOMException
     * @throws Exception
     */
    public function testGenerateArchive(): void
    {
        $pdf_documents = [
            'file1.pdf' => 'content1',
            'file2.pdf' => 'content2',
        ];
        $enveloppe = $this->createEnveloppe();
        $xml = $this->archiveGenerator->generateXML($enveloppe);
        $archiveName = $this->archiveGenerator->generateArchive($pdf_documents, $xml, 'LBRCL', 'BE003');
        static::assertMatchesRegularExpression('/^ENVOI-PJ-BPG-(43|45)-\w{1,5}-\w{1,5}-\d{6}-\d{14}.tar.gz.gpg$/', $archiveName);
        static::assertFileExists($archiveName);

        $absolutePath = realpath($archiveName);
        echo $absolutePath; // Outputs the absolute path to the archive file
    }
}
