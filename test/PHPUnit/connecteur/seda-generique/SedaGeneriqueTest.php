<?php

require_once __DIR__ . "/../../../../connecteur/seda-generique/lib/FluxDataTestSedaGenerique.class.php";

class SedaGeneriqueTest extends PastellTestCase
{
    use CurlUtilitiesTestTrait;

    public function testNominal()
    {
        $this->mockCurl([
            "http://seda-generator:8080//generate" => "OK"
        ]);

        $curlWrapper = $this->getObjectInstancier()->getInstance(CurlWrapperFactory::class)->getInstance();
        $curlWrapper->expects($this->any())
            ->method('setJsonPostData')
            ->willReturnCallback(function ($json_data) {
                //file_put_contents(__DIR__."/fixtures/setJsonPostData_call.json", json_encode($json_data));
                $this->assertJsonStringEqualsJsonFile(
                    __DIR__ . "/fixtures/setJsonPostData_call.json",
                    json_encode($json_data)
                );

                return true;
            });

        $id_ce = $this->createConnector('seda-generique', 'SEDA generique')['id_ce'];
        $this->configureConnector($id_ce, [
            'seda_generator_url' => 'http://seda-generator:8080/',
            'archival_agency_identifier' => 'FRAD0001',
            'transferring_agency_identifier' => 'TOTO001'
        ]);

        $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
        $connecteurConfig->addFileFromData('data', 'data.json', json_encode([
            'archival_agency_identifier' => 'FRAD001',
            'commentaire' => 'Ceci est un commentaire',
            'titre' => 'Ceci est mon titre',
            'keywords' => "mot-cle1,nom,subject\nmot-cle2,prenom,subject",
            'files' => "fichier1,Mon super fichier\nfichier2,Mon second fichier"
        ]));

        /** @var SedaGenerique $sedaGeneriqueConnector */
        $sedaGeneriqueConnector = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $fluxDataSedaGenerique = new FluxDataTestSedaGenerique();
        $fluxDataSedaGenerique->addFileList(['fichier1','fichier2']);

        $id_d = $this->createDocument('actes-generique')['id_d'];
        $this->configureDocument($id_d, [
            'numero' => '12',
        ]);

        $docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $sedaGeneriqueConnector->setDocDonneesFormulaire($docDonneesFormulaire);

        $bordereau = $sedaGeneriqueConnector->getBordereauNG($fluxDataSedaGenerique);

        $this->assertContains("OK", $bordereau);
    }
}
