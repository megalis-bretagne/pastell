<?php


class GenerateBordereauSEDATest extends PHPUnit_Framework_TestCase {

	private $relax_ng_path;
	private $bordereau_seda_with_annotation;

	/** @var  AnnotationWrapper */
	private $annotationWrapper;

	protected function setUp() {
		parent::setUp();
		$this->relax_ng_path = __DIR__ . "/../fixtures/EMEG_PROFIL_PES_0002_v1_schema.rng";
		$this->bordereau_seda_with_annotation =
			$this->getBordereauSEDAWithAnnotation(
				$this->relax_ng_path,
				__DIR__ . "/../fixtures/EMEG_PROFIL_PES_0002_v1.5.xml"
			);

		$this->annotationWrapper = new AnnotationWrapper();
	}

	protected function generate(){
		$generateBordereauSEDA = new GenerateBordereauSEDA();
		return $generateBordereauSEDA->generate($this->bordereau_seda_with_annotation, $this->annotationWrapper);
	}

	private function getBordereauSEDAWithAnnotation($rng_file, $agape_file){
		$relaxNGImportAgapeAnnotation = new RelaxNgImportAgapeAnnotation();
		$relaxNG_with_annotation = $relaxNGImportAgapeAnnotation->importAnnotation(
			$rng_file, $agape_file
		);

		$generateXMLFromAnnotedRelaxNG = new GenerateXMLFromAnnotedRelaxNG(new RelaxNG());

		return $generateXMLFromAnnotedRelaxNG->generateFromRelaxNGString($relaxNG_with_annotation);
	}

	private function validateBordereau($bordereau_xml,$relax_ng_path){

		$sedaValidation = new SedaValidation();
		$is_valide = $sedaValidation->validateRelaxNG($bordereau_xml, $relax_ng_path);
		if (! $is_valide){
			print_r($sedaValidation->getLastErrors());
			echo $bordereau_xml;
		}
		$this->assertTrue($is_valide);

		$is_valide = $sedaValidation->validateSEDA($bordereau_xml);

		if (! $is_valide){
			print_r($sedaValidation->getLastErrors());
			echo $bordereau_xml;
		}
		$this->assertTrue($is_valide);
	}

	public function testRelaxNGValide() {
		$data_test = array(
			'date_ack_iso_8601'=>'2015-02-10',
			'date_debut_iso_8601'=>'2015-02-10',
			'archive_size_ko'=>12,
			'date_integ_iso_8601'=>'2016-01-01',
			'start_date' => '2016-01-02',
			'date_acquittement_iso_8601' => date("c"),
			'date_mandatement' => date("c"),
			"pes_aller" => "toto.xml",
			"identifiant_bordereau" => "toto",
			"fichier_reponse" => "test.xml",
			"fichier_pes"=>"aller.xml"
		);

		$fluxDataTest = new FluxDataTest($data_test);
        $fluxDataTest->setFileList("fichier_pes", "fichier_pes", "fichier_pes");

		$this->annotationWrapper->setFluxData($fluxDataTest);

		$xml = $this->generate();

		$this->validateBordereau($xml,$this->relax_ng_path);
	}

	public function testConnecteur(){
		$fluxDataTest = new FluxDataTest(array());
		$this->annotationWrapper->setFluxData($fluxDataTest);
		$this->annotationWrapper->setConnecteurInfo(array('service_versant_description'=>'FooBar'));
		$xml = $this->generate();
		$xmlFile = new XMLFile();
		$xml_result = $xmlFile->getFromString($xml);
		$this->assertEquals("FooBar", (string) $xml_result->{'TransferringAgency'}->{'Description'});
	}

	public function testSEDAV1(){
		$bordereau_seda_with_annotation = $this->getBordereauSEDAWithAnnotation(
			__DIR__."/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL_schema.rng",
			__DIR__."/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL.xml"
		);
		$annotationWrapper = new AnnotationWrapper();

		$generateBordereauSEDA = new GenerateBordereauSEDA();

		$connecteur_info = array(
			"nom_service_archive" => "Service d'Archive",
			"id_service_archive" => "archive01",
			'transfert_id' =>'12',
			'id_service_versant' => 'versant01',
			'nom_service_versant'=>'Service versant',
			'accord_versement' => 'AV001',
		);

		$data_test = array(
			'fichier_pes'=>'toto.xml',
			"pes_aller" => "toto.xml",
			"fichier_reponse" => "reponse.xml",
			'date_acquittement_iso_8601' => date('Y-m-d'),
			'start_date' => date('Y-m-d'),
			'date_mandatement' => date('c'),
			'archive_size_ko' => '2',
			'date_generation_acquit' => date('c'),
		);

		$fluxDataTest = new FluxDataTest($data_test);
        $fluxDataTest->setFileList("fichier_pes", "fichier_pes", "fichier_pes");
        $fluxDataTest->setFileList("fichier_reponse", "fichier_reponse", "fichier_reponse");

		$annotationWrapper->setFluxData($fluxDataTest);
		$annotationWrapper->setConnecteurInfo($connecteur_info);

		$bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

		$this->validateBordereau(
			$bordereau_xml,
			__DIR__."/../fixtures/Profil_PES_AP_TESTCONNECT_PASTELL_schema.rng"
		);
	}


	public function testWithArray(){
		$bordereau_seda_with_annotation =

		$this->getBordereauSEDAWithAnnotation(
			__DIR__."/../fixtures/test_tableau_schema.rng",
			__DIR__."/../fixtures/test_tableau.xml"
		);

		$annotationWrapper = new AnnotationWrapper();

		$generateBordereauSEDA = new GenerateBordereauSEDA();

		$connecteur_info = array(
		);

		$data_test = array(
			'test_tableau' => array('un','deux','trois')
		);

		$fluxDataTest = new FluxDataTest($data_test);

		$annotationWrapper->setFluxData($fluxDataTest);
		$annotationWrapper->setConnecteurInfo($connecteur_info);

		$bordereau_xml =  $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

		$xml = simplexml_load_string($bordereau_xml);

		$element = $xml->children(SedaValidation::SEDA_V_1_0_NS);
		$keyword_list  = $element->Archive->ContentDescription->Keyword;
		$this->assertEquals(3,count($keyword_list));

		$this->assertEquals("trois",((string)$keyword_list[2]->KeywordContent));
	}

	private function getBordereauWithIf($data_test){
		$bordereau_seda_with_annotation =
			$this->getBordereauSEDAWithAnnotation(
				__DIR__."/../fixtures/test_if_schema.rng",
				__DIR__."/../fixtures/test_if.xml"
			);

		$annotationWrapper = new AnnotationWrapper();

		$generateBordereauSEDA = new GenerateBordereauSEDA();

		$connecteur_info = array(
		);
		$fluxDataTest = new FluxDataTest($data_test);

		$annotationWrapper->setFluxData($fluxDataTest);
		$annotationWrapper->setConnecteurInfo($connecteur_info);

		return $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);
	}

	public function testWithIfTrue(){
		$data_test = array(
			'latest_date' => 'toto'
		);
		$bordereau_xml = $this->getBordereauWithIf($data_test);

		$xml = simplexml_load_string($bordereau_xml);
		$this->assertEquals(
			"toto",
			(string) $xml->children(SedaValidation::SEDA_V_1_0_NS)->Archive->ContentDescription->LatestDate
		);
	}

	public function testWithIfFalse(){
		$bordereau_xml = $this->getBordereauWithIf(array());

		$xml = simplexml_load_string($bordereau_xml);

		$this->assertEmpty(
			$xml->children(SedaValidation::SEDA_V_1_0_NS)->Archive->ContentDescription->LatestDate
		);
	}

	public function testBaliseOptionnel(){
		$bordereau_seda_with_annotation =
			$this->getBordereauSEDAWithAnnotation(
				__DIR__."/../fixtures/balise_optionnel_schema.rng",
				__DIR__."/../fixtures/balise_optionnel.xml"
			);
		$generateBordereauSEDA = new GenerateBordereauSEDA();
		$annotationWrapper = new AnnotationWrapper();


		$bordereau_xml = $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

		$this->validateBordereau(
			$bordereau_xml,
			__DIR__."/../fixtures/balise_optionnel_schema.rng"
		);

		$xml = simplexml_load_string($bordereau_xml);
		$this->assertEmpty($xml->children(SedaValidation::SEDA_V_1_0_NS)->Archive->ArchivalAgencyArchiveIdentifier);
	}
	
}