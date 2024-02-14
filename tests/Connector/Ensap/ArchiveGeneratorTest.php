<?php

namespace Connector\Ensap;

use DOMException;
use DonneesFormulaire;
use DonneesFormulaireFactory;
use Exception;
use Pastell\Connector\Ensap\ArchiveGenerator;
use Pastell\Connector\Ensap\parts\Assure;
use Pastell\Connector\Ensap\parts\Document;
use Pastell\Connector\Ensap\parts\Emetteur;
use Pastell\Connector\Ensap\parts\Enveloppe;
use Pastell\Connector\Ensap\parts\Gestionnaire;
use Pastell\Connector\Ensap\parts\Message;
use PastellTestCase;

class ArchiveGeneratorTest extends PastellTestCase
{
    private ArchiveGenerator $archiveGenerator;
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->archiveGenerator = $this->getObjectInstancier()->getInstance(ArchiveGenerator::class);
    }

    public function createEnveloppe(): Enveloppe
    {
        $donneesFormulaire = $this->getObjectInstancier()->getInstance(DonneesFormulaireFactory::class)->get();
        $donneesFormulaire->setData('date_document', '01092021');
        $donneesFormulaire->setData('titre_document', '1870194035088_13000920600010_BPaie_01092021.pdf');
        $donneesFormulaire->setData('siret_collectivite', '13000920600010');
        $donneesFormulaire->setData('matricule_agent', '12589');
        $donneesFormulaire->setData('nom_naissance_agent', 'BERNARD');
        $donneesFormulaire->setData('date_naissance_agent', '11121964');
        $donneesFormulaire->setData('statut_agent', 'T');

        $donneesFormulaire->setData('sstheme', '43');
        $donneesFormulaire->setData('nom_emetteurSRE', 'LBRCL');
        $donneesFormulaire->setData('code_emetteurSRE', 'LBRCL');
        return $this->archiveGenerator->generateEnveloppe($donneesFormulaire);
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
    }
}
