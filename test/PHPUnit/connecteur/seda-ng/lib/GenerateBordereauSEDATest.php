<?php

class GenerateBordereauSEDATest extends PHPUnit\Framework\TestCase
{
    /**
     * @var
     */
    private $relax_ng_path;
    /**
     * @var
     */
    private $bordereau_seda_with_annotation;

    /** @var  AnnotationWrapper */
    private $annotationWrapper;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->relax_ng_path = __DIR__ . "/../fixtures/EMEG_PROFIL_PES_0002_v1_schema.rng";
        $this->bordereau_seda_with_annotation =
            $this->getBordereauSEDAWithAnnotation(
                $this->relax_ng_path,
                __DIR__ . "/../fixtures/EMEG_PROFIL_PES_0002_v1.5.xml"
            );

        $this->annotationWrapper = new AnnotationWrapper();
    }

    /**
     * @return null|string|string[]
     * @throws Exception
     */
    protected function generate()
    {
        $generateBordereauSEDA = new GenerateBordereauSEDA();
        return $generateBordereauSEDA->generate($this->bordereau_seda_with_annotation, $this->annotationWrapper);
    }

    /**
     * @param $rng_file
     * @param $agape_file
     * @return string
     */
    private function getBordereauSEDAWithAnnotation($rng_file, $agape_file)
    {
        $relaxNGImportAgapeAnnotation = new RelaxNgImportAgapeAnnotation();
        $relaxNG_with_annotation = $relaxNGImportAgapeAnnotation->importAnnotation(
            $rng_file,
            $agape_file
        );

        $generateXMLFromAnnotedRelaxNG = new GenerateXMLFromAnnotedRelaxNG(new RelaxNG());

        return $generateXMLFromAnnotedRelaxNG->generateFromRelaxNGString($relaxNG_with_annotation);
    }

    /**
     * @param $bordereau_xml
     * @param $relax_ng_path
     */
    private function validateBordereau($bordereau_xml, $relax_ng_path)
    {

        $sedaValidation = new SedaValidation();
        $is_valide = $sedaValidation->validateRelaxNG($bordereau_xml, $relax_ng_path);
        if (! $is_valide) {
            print_r($sedaValidation->getLastErrors());
            echo $bordereau_xml;
        }
        $this->assertTrue($is_valide);

        $is_valide = $sedaValidation->validateSEDA($bordereau_xml);

        if (! $is_valide) {
            print_r($sedaValidation->getLastErrors());
            echo $bordereau_xml;
        }
        $this->assertTrue($is_valide);
    }

    /**
     * @throws Exception
     */
    public function testRelaxNGValide()
    {
        $data_test = [
            'date_ack_iso_8601' => '2015-02-10',
            'date_debut_iso_8601' => '2015-02-10',
            'archive_size_ko' => 12,
            'date_integ_iso_8601' => '2016-01-01',
            'start_date' => '2016-01-02',
            'date_acquittement_iso_8601' => date("c"),
            'date_mandatement' => date("c"),
            "pes_aller" => "toto.xml",
            "identifiant_bordereau" => "toto",
            "fichier_reponse" => "test.xml",
            "fichier_pes" => "aller.xml"
        ];

        $fluxDataTest = new FluxDataTest($data_test);
        $fluxDataTest->setFileList("fichier_pes", "fichier_pes", "fichier_pes");

        $this->annotationWrapper->setFluxData($fluxDataTest);

        $xml = $this->generate();

        $this->validateBordereau($xml, $this->relax_ng_path);
    }

    /**
     * @throws Exception
     */
    public function testConnecteur()
    {
        $fluxDataTest = new FluxDataTest([]);
        $this->annotationWrapper->setFluxData($fluxDataTest);
        $this->annotationWrapper->setConnecteurInfo(['service_versant_description' => 'FooBar']);
        $xml = $this->generate();
        $xmlFile = new XMLFile();
        $xml_result = $xmlFile->getFromString($xml);
        $this->assertEquals("FooBar", (string) $xml_result->{'TransferringAgency'}->{'Description'});
    }

    /**
     * @throws Exception
     */
    public function testSEDAV1()
    {
        $bordereau_seda_with_annotation = $this->getBordereauSEDAWithAnnotation(
            __DIR__ . "/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL_schema.rng",
            __DIR__ . "/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL.xml"
        );
        $annotationWrapper = new AnnotationWrapper();

        $generateBordereauSEDA = new GenerateBordereauSEDA();

        $connecteur_info = [
            "nom_service_archive" => "Service d'Archive",
            "id_service_archive" => "archive01",
            'transfert_id' => '12',
            'id_service_versant' => 'versant01',
            'nom_service_versant' => 'Service versant',
            'accord_versement' => 'AV001',
        ];

        $data_test = [
            'fichier_pes' => 'toto.xml',
            "pes_aller" => "toto.xml",
            "fichier_reponse" => "reponse.xml",
            'date_acquittement_iso_8601' => date('Y-m-d'),
            'start_date' => date('Y-m-d'),
            'date_mandatement' => date('c'),
            'archive_size_ko' => '2',
            'date_generation_acquit' => date('c'),
        ];

        $fluxDataTest = new FluxDataTest($data_test);
        $fluxDataTest->setFileList("fichier_pes", "fichier_pes", "fichier_pes");
        $fluxDataTest->setFileList("fichier_reponse", "fichier_reponse", "fichier_reponse");

        $annotationWrapper->setFluxData($fluxDataTest);
        $annotationWrapper->setConnecteurInfo($connecteur_info);

        $bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

        $this->validateBordereau(
            $bordereau_xml,
            __DIR__ . "/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL_schema.rng"
        );
    }


    /**
     * @throws Exception
     */
    public function testWithArray()
    {
        $bordereau_seda_with_annotation =

        $this->getBordereauSEDAWithAnnotation(
            __DIR__ . "/../fixtures/test_tableau_schema.rng",
            __DIR__ . "/../fixtures/test_tableau.xml"
        );

        $annotationWrapper = new AnnotationWrapper();

        $generateBordereauSEDA = new GenerateBordereauSEDA();

        $connecteur_info = [];

        $data_test = [
            'test_tableau' => ['un','deux','trois']
        ];

        $fluxDataTest = new FluxDataTest($data_test);

        $annotationWrapper->setFluxData($fluxDataTest);
        $annotationWrapper->setConnecteurInfo($connecteur_info);

        $bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

        $xml = simplexml_load_string($bordereau_xml);

        $element = $xml->children(SedaValidation::SEDA_V_1_0_NS);
        $keyword_list  = $element->{'Archive'}->{'ContentDescription'}->{'Keyword'};
        $this->assertCount(3, $keyword_list);

        $this->assertEquals("trois", ((string)$keyword_list[2]->KeywordContent));
    }

    /**
     * @param $data_test
     * @return null|string|string[]
     * @throws Exception
     */
    private function getBordereauWithIf($data_test)
    {
        $bordereau_seda_with_annotation =
            $this->getBordereauSEDAWithAnnotation(
                __DIR__ . "/../fixtures/test_if_schema.rng",
                __DIR__ . "/../fixtures/test_if.xml"
            );

        $annotationWrapper = new AnnotationWrapper();

        $generateBordereauSEDA = new GenerateBordereauSEDA();

        $connecteur_info = [];
        $fluxDataTest = new FluxDataTest($data_test);

        $annotationWrapper->setFluxData($fluxDataTest);
        $annotationWrapper->setConnecteurInfo($connecteur_info);

        return $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);
    }

    /**
     * @throws Exception
     */
    public function testWithIfTrue()
    {
        $data_test = [
            'latest_date' => 'toto'
        ];
        $bordereau_xml = $this->getBordereauWithIf($data_test);

        $xml = simplexml_load_string($bordereau_xml);
        $this->assertEquals(
            "toto",
            (string) $xml->children(SedaValidation::SEDA_V_1_0_NS)->{'Archive'}->{'ContentDescription'}->{'LatestDate'}
        );
    }

    /**
     * @throws Exception
     */
    public function testWithIfFalse()
    {
        $bordereau_xml = $this->getBordereauWithIf([]);

        $xml = simplexml_load_string($bordereau_xml);

        $this->assertEmpty(
            $xml->children(SedaValidation::SEDA_V_1_0_NS)->{'Archive'}->{'ContentDescription'}->{'LatestDate'}
        );
    }

    /**
     * @throws Exception
     */
    public function testBaliseOptionnel()
    {
        $bordereau_seda_with_annotation =
            $this->getBordereauSEDAWithAnnotation(
                __DIR__ . "/../fixtures/balise_optionnel_schema.rng",
                __DIR__ . "/../fixtures/balise_optionnel.xml"
            );
        $generateBordereauSEDA = new GenerateBordereauSEDA();
        $annotationWrapper = new AnnotationWrapper();


        $bordereau_xml = $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

        $this->validateBordereau(
            $bordereau_xml,
            __DIR__ . "/../fixtures/balise_optionnel_schema.rng"
        );

        $xml = simplexml_load_string($bordereau_xml);
        $this->assertEmpty($xml->children(SedaValidation::SEDA_V_1_0_NS)->{'Archive'}->{'ArchivalAgencyArchiveIdentifier'});
    }

    //Normalement, un repeat et un array peuvent cohabiter sur la même annotation pour répeter la balise

    /**
     * @throws Exception
     */
    public function testArrayRepeat()
    {
        $bordereau_seda_with_annotation =

            $this->getBordereauSEDAWithAnnotation(
                __DIR__ . "/../fixtures/test_array_repeat_schema.rng",
                __DIR__ . "/../fixtures/test_array_repeat.xml"
            );

        $annotationWrapper = new AnnotationWrapper();

        $generateBordereauSEDA = new GenerateBordereauSEDA();

        $connecteur_info = [];

        $data_test = [
            'langue' => ['fra','eng','deu']
        ];

        $fluxDataTest = new FluxDataTest($data_test);

        $annotationWrapper->setFluxData($fluxDataTest);
        $annotationWrapper->setConnecteurInfo($connecteur_info);

        $bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

        $xml = simplexml_load_string($bordereau_xml);

        $this->assertEquals('fra', $xml->children(SedaValidation::SEDA_V_1_0_NS)->{'Archive'}->{'DescriptionLanguage'}[0]);
        $this->assertEquals('eng', $xml->children(SedaValidation::SEDA_V_1_0_NS)->{'Archive'}->{'DescriptionLanguage'}[1]);
        $this->assertEquals('deu', $xml->children(SedaValidation::SEDA_V_1_0_NS)->{'Archive'}->{'DescriptionLanguage'}[2]);


        $this->validateBordereau(
            $bordereau_xml,
            __DIR__ . "/../fixtures/test_array_repeat_schema.rng"
        );
    }

    /**
     * @throws Exception
     */
    public function testArrayRepeatOneValue()
    {
        $bordereau_seda_with_annotation =

            $this->getBordereauSEDAWithAnnotation(
                __DIR__ . "/../fixtures/test_array_repeat_schema.rng",
                __DIR__ . "/../fixtures/test_array_repeat.xml"
            );

        $annotationWrapper = new AnnotationWrapper();

        $generateBordereauSEDA = new GenerateBordereauSEDA();

        $connecteur_info = [];

        $data_test = [
            'langue' => 'fra'
        ];

        $fluxDataTest = new FluxDataTest($data_test);

        $annotationWrapper->setFluxData($fluxDataTest);
        $annotationWrapper->setConnecteurInfo($connecteur_info);

        $bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

        $xml = simplexml_load_string($bordereau_xml);

        $this->assertEquals('fra', $xml->children(SedaValidation::SEDA_V_1_0_NS)->{'Archive'}->{'DescriptionLanguage'}[0]);

        $this->validateBordereau(
            $bordereau_xml,
            __DIR__ . "/../fixtures/test_array_repeat_schema.rng"
        );
    }


    /**
     * @throws Exception
     */
    public function testRelaxNGValide2()
    {

        $string_to_test = 'Dès Noël où un zéphyr haï me vêt de glaçons würmiens je dîne d’exquis rôtis de bœuf au kir à l’aÿ d’âge mûr & cætera';

        $bordereau_seda_with_annotation =

            $this->getBordereauSEDAWithAnnotation(
                __DIR__ . "/../fixtures/profil_test_schema.rng",
                __DIR__ . "/../fixtures/profil_test.xml"
            );

        $annotationWrapper = new AnnotationWrapper();

        $generateBordereauSEDA = new GenerateBordereauSEDA();

        $connecteur_info = [
            'service_versant' => $string_to_test
        ];

        $data_test = [
            'langue' => 'fra'
        ];

        $fluxDataTest = new FluxDataTest($data_test);

        $annotationWrapper->setFluxData($fluxDataTest);
        $annotationWrapper->setConnecteurInfo($connecteur_info);

        $bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

        $xml = simplexml_load_string($bordereau_xml);

        $this->assertEquals(
            $string_to_test,
            strval($xml->children(SedaValidation::SEDA_V_0_2_NS)->{'TransferringAgency'}->{'Identification'})
        );
    }

    /**
     * @throws Exception
     */
    public function testRepeatInRepeat()
    {
        $bordereau_seda_with_annotation =
            $this->getBordereauSEDAWithAnnotation(
                __DIR__ . "/../fixtures/repeat_in_repeat_schema.rng",
                __DIR__ . "/../fixtures/repeat_in_repeat.xml"
            );

        $annotationWrapper = new AnnotationWrapper();
        $generateBordereauSEDA = new GenerateBordereauSEDA();

        $fluxDataTest = new FluxDataTestRepeat();

        $annotationWrapper->setFluxData($fluxDataTest);
        $annotationWrapper->setConnecteurInfo([]);

        $bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

        $this->validateBordereau(
            $bordereau_xml,
            __DIR__ . "/../fixtures/repeat_in_repeat_schema.rng"
        );

        $xml = simplexml_load_string($bordereau_xml);
        $children = $xml->children(SedaValidation::SEDA_V_1_0_NS);
        $children->{'Date'} = 'NOT TESTABLE';

        $this->assertStringEqualsFile(__DIR__ . "/../fixtures/bordereau-repeat-in-repat.xml", $xml->asXML());
    }


    /**
     * @throws Exception
     */
    public function testConnecteurInfo()
    {
        $bordereau_seda_with_annotation =
            $this->getBordereauSEDAWithAnnotation(
                __DIR__ . "/../fixtures/connecteur_info_schema.rng",
                __DIR__ . "/../fixtures/connecteur_info.xml"
            );
        $annotationWrapper = new AnnotationWrapper();
        $generateBordereauSEDA = new GenerateBordereauSEDA();

        $fluxDataTest = new FluxDataTestConnecteurInfo();
        $connecteur_content = [
            'id_service_archive' => 'ARCHIVE',
            'id_producteur_hors_rh' => 'TOTO',
            'id_producteur_rh' => 'POUM'
        ];
        $fluxDataTest->setConnecteurContent($connecteur_content);

        $annotationWrapper->setFluxData($fluxDataTest);
        $annotationWrapper->setConnecteurInfo($connecteur_content);

        $bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

        //file_put_contents(__DIR__."/../fixtures/connecteur_info_bordereau.xml",$bordereau_xml);
        $this->assertStringEqualsFile(__DIR__ . "/../fixtures/connecteur_info_bordereau.xml", $bordereau_xml);
    }


    /**
     * @throws Exception
     */
    public function testSEDAV1WithControleCaractereInFileName()
    {
        $bordereau_seda_with_annotation = $this->getBordereauSEDAWithAnnotation(
            __DIR__ . "/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL_schema.rng",
            __DIR__ . "/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL.xml"
        );
        $annotationWrapper = new AnnotationWrapper();

        $generateBordereauSEDA = new GenerateBordereauSEDA();

        $connecteur_info = [
            "nom_service_archive" => "Service d'Archive",
            "id_service_archive" => "archive01",
            'transfert_id' => '12',
            'id_service_versant' => 'versant01',
            'nom_service_versant' => 'Service versant',
            'accord_versement' => 'AV001',
        ];

        $data_test = [
            'fichier_pes' => 'foo & bar',
            "pes_aller" => "toto.xml",
            "fichier_reponse" => "reponse & toto.xml", //Ici test d'un fichier avec un &
            'date_acquittement_iso_8601' => date('Y-m-d'),
            'start_date' => date('Y-m-d'),
            'date_mandatement' => date('c'),
            'archive_size_ko' => '2',
            'date_generation_acquit' => date('c'),
        ];

        $fluxDataTest = new FluxDataTest($data_test);
        $fluxDataTest->setFileList("fichier_pes", "fichier_pes", "fichier_pes");
        $fluxDataTest->setFileList("fichier_reponse", "fichier_reponse", "fichier_reponse");

        $annotationWrapper->setFluxData($fluxDataTest);
        $annotationWrapper->setConnecteurInfo($connecteur_info);

        $bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

        $this->validateBordereau(
            $bordereau_xml,
            __DIR__ . "/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL_schema.rng"
        );
    }

    public function testSEDAV1WithUTF8InFileName()
    {
        $bordereau_seda_with_annotation = $this->getBordereauSEDAWithAnnotation(
            __DIR__ . "/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL_schema.rng",
            __DIR__ . "/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL.xml"
        );
        $annotationWrapper = new AnnotationWrapper();

        $generateBordereauSEDA = new GenerateBordereauSEDA();

        $connecteur_info = [
            "nom_service_archive" => "Service d'Archive",
            "id_service_archive" => "archive01",
            'transfert_id' => '12',
            'id_service_versant' => 'versant01',
            'nom_service_versant' => 'Service versant',
            'accord_versement' => 'AV001',
        ];

        $data_test = [
            'fichier_pes' => 'foo & bar',
            "pes_aller" => "toto.xml",
            "fichier_reponse" => "réponse.xml",
            'date_acquittement_iso_8601' => date('Y-m-d'),
            'start_date' => date('Y-m-d'),
            'date_mandatement' => date('c'),
            'archive_size_ko' => '2',
            'date_generation_acquit' => date('c'),
        ];

        $fluxDataTest = new FluxDataTest($data_test);
        $fluxDataTest->setFileList("fichier_pes", "fichier_pes", "fichier_pes");
        $fluxDataTest->setFileList("fichier_reponse", "fichier_reponse", "fichier_reponse");

        $annotationWrapper->setFluxData($fluxDataTest);
        $annotationWrapper->setConnecteurInfo($connecteur_info);
        $annotationWrapper->setTranslitFilenameRegExp('#$a#'); //expression never match

        $bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

        $this->validateBordereau(
            $bordereau_xml,
            __DIR__ . "/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL_schema.rng"
        );
        $xml = simplexml_load_string($bordereau_xml);
        $this->assertEquals("réponse.xml", $xml->Archive->ArchiveObject[1]->Document->Attachment['filename']);
    }
}
