<?php

namespace Pastell\Tests\Connector\Ensap;

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
use PhpParser\Node\Expr\Throw_;

class ArchiveGeneratorTest extends PastellTestCase
{
    private ArchiveGenerator $archiveGenerator;
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->archiveGenerator = $this->getObjectInstancier()->getInstance(ArchiveGenerator::class);
    }

    public function getFormulaire(): DonneesFormulaire
    {
        $donneesFormulaire = $this->getObjectInstancier()->getInstance(DonneesFormulaireFactory::class)->get('toto', 'test');
        $donneesFormulaire->setData('date_document', '01092021');
        $donneesFormulaire->setData('titre_document', '1870194035088_13000920600010_BPaie_01092021.pdf');
        $donneesFormulaire->setData('siret_collectivite', '13000920600010');
        $donneesFormulaire->setData('matricule_agent', '12589');
        $donneesFormulaire->setData('nom_naissance_agent', 'BERNARD');
        $donneesFormulaire->setData('date_naissance_agent', '11121964');
        $donneesFormulaire->setData('statut_agent', 'T');
        $donneesFormulaire->setData('iban_agent', '7758');
        $donneesFormulaire->setData('nir_agent', '1641216251234');
        $donneesFormulaire->setData('sexe', 'H');

        $donneesFormulaire->setData('sstheme', '43');
        $donneesFormulaire->setData('nom_emetteur_sre', 'LBRCL');
        $donneesFormulaire->setData('code_emetteur_sre', 'LBRCL');
        return $donneesFormulaire;
    }

    /**
     * @throws DOMException
     */
    public function testGenerateXML(): void
    {
        $enveloppe = $this->archiveGenerator->generateEnveloppe($this->getFormulaire());
        $xml = $this->archiveGenerator->generateXML($enveloppe);

        static::assertTrue(strpos($xml, '<theme>') < strpos($xml, '<sstheme>'));
        static::assertTrue(strpos($xml, '<sstheme>') < strpos($xml, '<date_document>'));
        static::assertTrue(strpos($xml, '<date_document>') < strpos($xml, '</document>'));
    }

    /**
     * @throws DOMException
     */
    public function testValidateXML(): void
    {
        $enveloppe = $this->archiveGenerator->generateEnveloppe($this->getFormulaire());
        $xml = $this->archiveGenerator->generateXML($enveloppe);
        static::assertTrue($this->archiveGenerator->validateXML($xml));
    }

    /**
     * @throws Exception
     */
    public function testGenerateArchiveName(): void
    {
        $formulaire = $this->getFormulaire();
        $archiveName = $this->archiveGenerator->generateArchiveName($formulaire);
        static::assertMatchesRegularExpression('/^ENVOI-PJ-BPG-(43|45)-\w{1,5}-\w{1,5}-\d{6}-\d{14}$/', $archiveName);
    }

    /**
     * @throws Exception
     */
    public function testGenerateArchive(): void
    {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public function testEncryptArchive(): void
    {
        throw new Exception('Not implemented');
    }
}
