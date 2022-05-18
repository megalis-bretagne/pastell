<?php

declare(strict_types=1);

use Pastell\Seda\Message\SedaMessageBuilder;

class SedaGeneriqueTest extends PastellTestCase
{
    use CurlUtilitiesTestTrait;

    private TmpFolder $tmpFolder;
    private string $tmp_folder = '';

    /**
     * @throws Exception
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->tmpFolder = new TmpFolder();
        $this->tmp_folder = $this->tmpFolder->create();
        mkdir($this->tmp_folder . '/workspace');
    }

    public function __destruct()
    {
        $this->tmpFolder->delete($this->tmp_folder);
    }

    private function setCurl(callable $returnCallback): void
    {
        $this->mockCurl([
            "http://seda-generator:8080/generate" => "OK",
        ]);

        $curlWrapper = $this->getObjectInstancier()->getInstance(CurlWrapperFactory::class)->getInstance();
        $curlWrapper->expects($this->any())
            ->method('setJsonPostData')
            ->willReturnCallback($returnCallback);
    }

    private function createSedaGeneriqueConnector(): int
    {
        $sedaMessageBuilder = new SedaMessageBuilder($this->tmpFolder);
        $sedaMessageBuilder->setIdGeneratorFunction(function () {
            static $i = 0;
            $i++;
            return "NOT_TESTABLE_$i";
        });
        $this->getObjectInstancier()->setInstance(SedaMessageBuilder::class, $sedaMessageBuilder);
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
     * @throws NotFoundException
     * @throws DonneesFormulaireException
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
            'objet' => 'Nomination du colonel Moutarde',
        ]);
        $donneesFormulaire->addFileFromData('arrete', 'actes.pdf', 'foo bar');
        $donneesFormulaire->addFileFromData(
            'autre_document_attache',
            'annexe1.pdf',
            'foo bar baz',
            0
        );
        $donneesFormulaire->addFileFromData(
            'autre_document_attache',
            'annexe2.pdf',
            'buz',
            1
        );

        $donneesFormulaire->addFileFromData(
            'bordereau',
            'borderau.pdf',
            'bordcontent'
        );
        $donneesFormulaire->addFileFromCopy(
            'aractes',
            'aractes.xml',
            __DIR__ . '/fixtures/202010281531-ar-actes.xml'
        );

        $donneesFormulaire->addFileFromCopy(
            'file_zip',
            '7756W3_9.zip',
            __DIR__ . '/fixtures/7756W3_9.zip'
        );

        $donneesFormulaire->addFileFromCopy(
            'file_xml',
            'PESALLER.xml',
            __DIR__ . '/fixtures/HELIOS_SIMU_ALR2_1595923133_1646706116.xml'
        );
        return $id_d;
    }

    public function caseProvider(): array
    {
        $all_dir = scandir(__DIR__ . '/seda-test-cases');
        $all_dir = array_diff($all_dir, ['.', '..']);
        $result = [];
        foreach ($all_dir as $dir) {
            $result[$dir] = [__DIR__ . '/seda-test-cases/' . $dir];
        }
        return $result;
    }

    /**
     * @throws DonneesFormulaireException
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     * @dataProvider caseProvider
     */
    public function testAllCase(string $folder): void
    {
        $this->setCurl(function (array $json_data) use ($folder) {
            $json_content = json_encode($json_data, JSON_THROW_ON_ERROR);
//            file_put_contents(
//                $folder . '/expected_call.json',
//                json_encode($json_data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
//            );
            $this->assertJsonStringEqualsJsonFile(
                $folder . '/expected_call.json',
                $json_content
            );
            return true;
        });

        $id_ce = $this->createSedaGeneriqueConnector();

        $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);

        $connecteurConfig->addFileFromCopy('files', 'file.xml', $folder . '/files.xml');
        $connecteurConfig->addFileFromCopy('data', 'data.json', $folder . '/data.json');

        /** @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $id_d = $this->createDossier();
        $docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $sedaGeneriqueConnector->setDocDonneesFormulaire($docDonneesFormulaire);

        $bordereau = $sedaGeneriqueConnector->getBordereau(new FluxDataTestSedaGenerique());
        $this->assertStringContainsString('OK', $bordereau);
    }

    /**
     * @throws DonneesFormulaireException
     * @throws JsonException
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testWhenAKeywordIsAssociatedWithAFile(): void
    {
        $this->setCurl(function (array $json_data) {
            $this->assertJsonStringEqualsJsonString(
                '{"Keywords":[],"ArchiveUnits":[],"Files":[]}',
                json_encode($json_data, JSON_THROW_ON_ERROR)
            );
            return true;
        });
        $id_ce = $this->createSedaGeneriqueConnector();
        $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
        $connecteurConfig->addFileFromData(
            'data',
            'data.json',
            json_encode(
                [
                    'commentaire' => '{{ arrete }}',
                ],
                JSON_THROW_ON_ERROR
            )
        );
        $connecteurConfig->addFileFromCopy('files', 'file.xml', __DIR__ . '/seda-test-cases/empty/files.xml');

        /** @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $id_d = $this->createDossier();
        $docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $sedaGeneriqueConnector->setDocDonneesFormulaire($docDonneesFormulaire);

        $this->expectExceptionMessage(
            'Erreur sur le template {{ arrete }} : An exception has been thrown during the rendering of a template ("Array to string conversion")'
        );
        $this->expectException(UnrecoverableException::class);
        $sedaGeneriqueConnector->getBordereau(new FluxDataTestSedaGenerique());
    }

    /**
     * @throws DonneesFormulaireException
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testWhenGeneratorReturnANon200HttpCode(): void
    {
        $this->mockCurl(['http://seda-generator:8080/generate' => 'KO'], 503);
        $id_ce = $this->createSedaGeneriqueConnector();
        /** @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $id_d = $this->createDossier();
        $docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $sedaGeneriqueConnector->setDocDonneesFormulaire($docDonneesFormulaire);

        $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
        $connecteurConfig->addFileFromCopy('files', 'file.xml', __DIR__ . '/seda-test-cases/empty/files.xml');
        $connecteurConfig->addFileFromCopy('data', 'data.json', __DIR__ . '/seda-test-cases/empty/data.json');

        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage('SedaGenerator did not return a 200 response. Code HTTP: 503.');
        $sedaGeneriqueConnector->getBordereau(new FluxDataTestSedaGenerique());
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testWhenConnectionIsOk(): void
    {
        $this->mockCurl(['http://seda-generator:8080/version' => '{"version":"0.3.1"}']);
        $id_ce = $this->createSedaGeneriqueConnector();
        /* @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $this->assertSame('{"version":"0.3.1"}', $sedaGeneriqueConnector->testConnexion());
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testWhenConnectionIsNotOk(): void
    {
        $this->mockCurl(['http://seda-generator:8080/version' => 'KO'], 404);
        $id_ce = $this->createSedaGeneriqueConnector();
        /* @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage('SedaGenerator did not return a 200 response. Code HTTP: 404.');
        $sedaGeneriqueConnector->testConnexion();
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testWhithURLinGlobalConnector(): void
    {
        $this->mockCurl(['http://seda-generator-in-global:8080/version' => '{"version":"0.6.1"}']);

        $id_ce = $this->createConnector('generateur-seda', 'SEDA generique', 0)['id_ce'];
        $this->configureConnector(
            $id_ce,
            [
                'seda_generator_url' => 'http://seda-generator-in-global:8080/',
            ],
            0
        );
        $this->associateFluxWithConnector($id_ce, 'test', 'Generateur SEDA', 0);

        $id_ce = $this->createSedaGeneriqueConnector();
        $this->configureConnector($id_ce, ['seda_generator_url' => '']);

        /* @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $this->assertSame('{"version":"0.6.1"}', $sedaGeneriqueConnector->testConnexion());
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testWithoutURL(): void
    {
        $id_ce = $this->createSedaGeneriqueConnector();
        $this->configureConnector($id_ce, ['seda_generator_url' => '']);

        /* @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);

        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage(
            "L'URL du générateur n'a pas été trouvé. Avez-vous pensé à créer un connecteur global Generateur SEDA et à l'associer ?"
        );
        $sedaGeneriqueConnector->testConnexion();
    }
}
