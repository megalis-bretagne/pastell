<?php

use Twig\Error\RuntimeError;

require_once __DIR__ . "/../../../../connecteur/generateur-seda/lib/FluxDataTestSedaGenerique.class.php";

class SedaGeneriqueTest extends PastellTestCase
{
    use CurlUtilitiesTestTrait;

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
     * @param array|null $config
     * @return int
     * @throws Exception
     */
    private function createSedaGeeneriqueConnector(array $config = null): int
    {
        $id_ce = $this->createConnector('generateur-seda', 'SEDA generique')['id_ce'];
        $this->configureConnector($id_ce, [
            'seda_generator_url' => 'http://seda-generator:8080/',
            'archival_agency_identifier' => 'FRAD0001',
            'transferring_agency_identifier' => 'TOTO001'
        ]);

        if ($config) {
            $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
            $connecteurConfig->addFileFromData('data', 'data.json', json_encode($config));
        }
        return $id_ce;
    }

    private function createDossier(): string
    {
        $id_d = $this->createDocument('actes-generique')['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData([
            'numero_de_lacte' => '12',
        ]);
        $donneesFormulaire->addFileFromData('arrete', "actes.pdf", "foo bar");

        return $id_d;
    }

    private function getFluxDataSedaGenerique(): FluxDataTestSedaGenerique
    {
        $fluxDataSedaGenerique = new FluxDataTestSedaGenerique();
        $fluxDataSedaGenerique->addFileList(['fichier1', 'fichier2']);
        return $fluxDataSedaGenerique;
    }


    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testNominal()
    {
        $this->setCurl(function ($json_data) {
            //file_put_contents(__DIR__."/fixtures/setJsonPostData_call.json", json_encode($json_data));
            $this->assertJsonStringEqualsJsonFile(
                __DIR__ . "/fixtures/setJsonPostData_call.json",
                json_encode($json_data)
            );
            return true;
        });

        $id_ce = $this->createSedaGeeneriqueConnector([
            'archival_agency_identifier' => 'FRAD001',
            'commentaire' => 'Ceci est un commentaire',
            'titre' => 'Ceci est mon titre',
            'keywords' => "mot-cle1,nom,subject\nmot-cle2,prenom,subject",
            'files' => "fichier1,Mon super fichier\nfichier2,Mon second fichier"
        ]);

        /** @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);

        $id_d = $this->createDossier();
        $docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $sedaGeneriqueConnector->setDocDonneesFormulaire($docDonneesFormulaire);

        $fluxDataSedaGenerique = $this->getFluxDataSedaGenerique();
        $bordereau = $sedaGeneriqueConnector->getBordereauNG($fluxDataSedaGenerique);
        $this->assertStringContainsString("OK", $bordereau);
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testEmpty()
    {
        $this->setCurl(function ($json_data) {
            $this->assertJsonStringEqualsJsonString(
                '{"Keywords":[],"ArchiveUnits":[]}',
                json_encode($json_data)
            );

            return true;
        });

        $id_ce = $this->createSedaGeeneriqueConnector();

        /** @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);

        $id_d = $this->createDossier();
        $docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $sedaGeneriqueConnector->setDocDonneesFormulaire($docDonneesFormulaire);

        $fluxDataSedaGenerique = $this->getFluxDataSedaGenerique();
        $bordereau = $sedaGeneriqueConnector->getBordereauNG($fluxDataSedaGenerique);
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
                '{"Keywords":[],"ArchiveUnits":[]}',
                json_encode($json_data)
            );
            return true;
        });
        $id_ce = $this->createSedaGeeneriqueConnector(
            [
                'commentaire' => '{{ arrete }}',
            ]
        );
        /** @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $id_d = $this->createDossier();
        $docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $sedaGeneriqueConnector->setDocDonneesFormulaire($docDonneesFormulaire);
        $fluxDataSedaGenerique = $this->getFluxDataSedaGenerique();
        $this->expectExceptionMessage('An exception has been thrown during the rendering of a template ("Array to string conversion")');
        $this->expectException(RuntimeError::class);
        $sedaGeneriqueConnector->getBordereauNG($fluxDataSedaGenerique);
    }

    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testWhenAFileIsAssociateWithAKeyword()
    {
        $this->setCurl(function ($json_data) {
            $this->assertJsonStringEqualsJsonString(
                '{"Keywords":[],"ArchiveUnits":[]}',
                json_encode($json_data)
            );
            return true;
        });
        $id_ce = $this->createSedaGeeneriqueConnector(
            [
                'files' => 'objet,test'
            ]
        );
        /** @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $id_d = $this->createDossier();
        $docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $sedaGeneriqueConnector->setDocDonneesFormulaire($docDonneesFormulaire);
        $fluxDataSedaGenerique = $this->getFluxDataSedaGenerique();
        $sedaGeneriqueConnector->getBordereauNG($fluxDataSedaGenerique);
    }
}
