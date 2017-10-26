<?php

class AnnotationWrapperTest extends LegacyPHPUnit_Framework_TestCase {

	/** @var  AnnotationWrapper */
	private $annotationWrapper;

	protected function setUp() {
		$this->annotationWrapper = new AnnotationWrapper();
	}

	private function assertAnnotation($expeted_output,$input){
		$this->assertEquals($expeted_output,$this->annotationWrapper->wrap($input)->string);
	}

	public function testCommandNotExist(){
		$this->setExpectedException("Exception","La commande « toto » est inconnue sur ce Pastell");
		$this->assertAnnotation("","{{pastell:toto}}");
	}

	public function testWrap(){
		$this->assertAnnotation("","totot");
	}

	public function testWrapString(){
		$this->assertAnnotation("Archive transfert","{{pastell:string:Archive transfert}}");
	}

	public function testWrapOneClosingBracket(){
		$this->assertAnnotation("{toto}toto", "{{pastell:string:{toto}toto}}");
	}

	public function testWrapOneClosingBracketAtEnd(){
		$this->assertAnnotation("{toto", "{{pastell:string:{toto}}}");
	}

	public function testDoubleWrap(){
		$this->assertAnnotation("pimpoum","{{pastell:string:pim}}-pam-{{{pastell:string:poum}}");
	}

	public function testDateNow(){
		$date = $this->annotationWrapper->wrap("{{pastell:now}}")->string;
		$this->assertRegExp("#^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}#",$date); //2012-04-04T16:05:30Z
	}

	public function testIntegrity(){
		
		$fluxDataTest = new FluxDataTest(array());
        $fluxDataTest->setFileList("toto", "toto", "toto");

		$this->annotationWrapper->setFluxData($fluxDataTest);

		$integrity = $this->annotationWrapper->wrap("{{pastell:integrity}}")->string;
		$xmlFile = new XMLFile();
		$xml = $xmlFile->getFromString("<element>".$integrity."</element>");

		$this->assertEquals(
			AnnotationWrapper::SHA256_URI,
			(string) $xml->{'Integrity'}[0]->{'Contains'}['algorithme']
		);
	}

	public function testConnecteur(){
		$this->annotationWrapper->setConnecteurInfo(array("foo"=>"bar"));
		$this->assertAnnotation("bar", "{{pastell:connecteur:foo}}");
	}

	public function testConnecteurNotExists(){
		$this->assertAnnotation("", "{{pastell:connecteur:foo}}");
	}

	public function testTripleWrap(){
		$this->annotationWrapper->setConnecteurInfo(array("a"=>"bar"));
		$wrap = $this->annotationWrapper->wrap("{{pastell:connecteur:a}}{{pastell:string:-}}{{pastell:string:c}}");
		$this->assertEquals("bar-c", $wrap->string);
	}

	public function testCompteurJour(){
		$this->annotationWrapper->setCompteurJour("foo");
		$this->assertAnnotation("foo", "{{pastell:compteurJour}}");
	}

	public function testFluxWrap(){
		$fluxDataTest = new FluxDataTest(array("foo"=>"bar"));
		$this->annotationWrapper->setFluxData($fluxDataTest);
		$this->assertAnnotation("bar", "{{pastell:flux:foo}}");
	}

	public function testBizarre1(){
		$this->assertAnnotation("Domaine ***",'Récupérer le nom de la balise <Domaine> ("PES_DepenseAller" ou "PES_Recette_Aller" ou...) {{pastell:string:Domaine ***}}');
	}

	public function testSha256Command(){
		$fluxDataTest = new FluxDataTest(array());
        $fluxDataTest->setFileList("fichier_test", "fichier_test", "fichier_test");
		$this->annotationWrapper->setFluxData($fluxDataTest);
		$annotationReturn = $this->annotationWrapper->wrap("{{pastell:sha256:fichier_test}}");
		$this->assertEquals(
			AnnotationWrapper::SHA256_URI,
			$annotationReturn->node_attributes['algorithme']
		);
	}
}