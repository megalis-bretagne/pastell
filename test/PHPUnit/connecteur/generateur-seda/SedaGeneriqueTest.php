<?php

use Twig\Error\RuntimeError;

require_once __DIR__ . "/../../../../connecteur/generateur-seda/lib/FluxDataTestSedaGenerique.class.php";

class SedaGeneriqueTest extends PastellTestCase
{
    use CurlUtilitiesTestTrait;

    private $tmp_folder = "";

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $tmpFolder = new TmpFolder();
        $this->tmp_folder = $tmpFolder->create();
        mkdir($this->tmp_folder . "/workspace");
    }

    public function __destruct()
    {
        $tmpFolder = new TmpFolder();
        $tmpFolder->delete($this->tmp_folder);
    }

    private function setCurl(callable $returnCallback): void
    {
        $this->mockCurl([
            "http://seda-generator:8080/generate" => "OK"
        ]);

        $curlWrapper = $this->getObjectInstancier()->getInstance(CurlWrapperFactory::class)->getInstance();
        $curlWrapper->expects($this->any())
            ->method('setJsonPostData')
            ->willReturnCallback($returnCallback);
    }

    /**
     * @return int
     * @throws Exception
     */
    private function createSedaGeneriqueConnector(): int
    {
        $id_ce = $this->createConnector('generateur-seda', 'SEDA generique')['id_ce'];
        $this->configureConnector($id_ce, [
            'seda_generator_url' => 'http://seda-generator:8080/',
        ]);
        return $id_ce;
    }

    public function getEmulatedDisk(): string
    {
        return $this->tmp_folder;
    }


    /**
     * @return string
     * @throws NotFoundException
     * @throws Exception
     */
    private function createDossier(): string
    {
        $id_d = $this->createDocument('actes-generique')['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData([
            'numero_de_lacte' => '12',
            'date_de_lacte' => '2020-12-04',
            'acte_nature' => 'Délibération',
            'classification' => '1.1 Titulaire de la fonction publique territoriale',
            'objet' => 'Nomination du colonel Moutarde'

        ]);
        $donneesFormulaire->addFileFromData('arrete', "actes.pdf", "foo bar");
        $donneesFormulaire->addFileFromData(
            'autre_document_attache',
            "annexe1.pdf",
            "foo bar baz",
            0
        );
        $donneesFormulaire->addFileFromData(
            'autre_document_attache',
            "annexe2.pdf",
            "buz",
            1
        );

        $donneesFormulaire->addFileFromData(
            'bordereau',
            "borderau.pdf",
            "bordcontent"
        );
        $donneesFormulaire->addFileFromCopy(
            'aractes',
            "aractes.xml",
            __DIR__ . "/fixtures/202010281531-ar-actes.xml"
        );

        $donneesFormulaire->addFileFromCopy(
            'file_zip',
            "7756W3_9.zip",
            __DIR__ . "/fixtures/7756W3_9.zip"
        );

        return $id_d;
    }

    public function caseProvider(): array
    {
        $all_dir = scandir(__DIR__ . "/seda-test-cases");
        $all_dir = array_diff($all_dir, [".", ".."]);
        $result = [];
        foreach ($all_dir as $dir) {
            $result[$dir] = [__DIR__ . "/seda-test-cases/" . $dir];
        }
        return $result;
    }

    /**
     * @param string $folder
     * @throws DonneesFormulaireException
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     * @dataProvider caseProvider
     */
    public function testAllCase(string $folder)
    {
        $this->setCurl(function ($json_data) use ($folder) {
            $json_content = json_encode($json_data);
            //file_put_contents($test_folder . "/expected_call.json",json_encode($json_data));
            $this->assertJsonStringEqualsJsonFile(
                $folder . "/expected_call.json",
                $json_content
            );
            return true;
        });

        $id_ce = $this->createSedaGeneriqueConnector();

        $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);

        $connecteurConfig->addFileFromCopy('files', 'file.xml', $folder . "/files.xml");
        $connecteurConfig->addFileFromCopy('data', 'data.json', $folder . "/data.json");

        /** @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $sedaGeneriqueConnector->setIdGeneratorFunction(function () {
            static $i = 0;
            $i++;
            return "NOT_TESTABLE_$i";
        });
        $id_d = $this->createDossier();
        $docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $sedaGeneriqueConnector->setDocDonneesFormulaire($docDonneesFormulaire);

        $bordereau = $sedaGeneriqueConnector->getBordereauNG(new FluxDataTestSedaGenerique());
        $this->assertStringContainsString("OK", $bordereau);
    }

    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testWhenAKeywordIsAssociatedWithAFile()
    {
        $this->setCurl(function ($json_data) {
            $this->assertJsonStringEqualsJsonString(
                '{"Keywords":[],"ArchiveUnits":[],"Files":[]}',
                json_encode($json_data)
            );
            return true;
        });
        $id_ce = $this->createSedaGeneriqueConnector();
        $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
        $connecteurConfig->addFileFromData('data', 'data.json', json_encode([
            'commentaire' => '{{ arrete }}',
        ]));

        /** @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $id_d = $this->createDossier();
        $docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $sedaGeneriqueConnector->setDocDonneesFormulaire($docDonneesFormulaire);
        $this->expectExceptionMessage('Erreur sur le template {{ arrete }} : An exception has been thrown during the rendering of a template ("Array to string conversion")');
        $this->expectException(UnrecoverableException::class);
        $sedaGeneriqueConnector->getBordereauNG(new FluxDataTestSedaGenerique());
    }
}
