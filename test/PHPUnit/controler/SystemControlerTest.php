<?php 

class SystemControlerTest extends ControlerTestCase {

	/** @var  SystemControler */
	private $systemControler;

	public function setUp(){
		parent::setUp();
		$this->systemControler = $this->getControlerInstance("SystemControler");
	}
	

	public function testFluxDetailAction(){
		//$_GET['id'] = 'actes-generique';
		$this->expectOutputRegex("##");
		$this->systemControler->fluxDetailAction();
	}
	
	public function testIndex() {
		$this->expectOutputRegex("#Test de l'environnement#");
		$this->systemControler->indexAction();
	}
	
	public function testChangelog(){
        $this->expectOutputRegex("#Journal des modifications#");
	    $this->systemControler->changelogAction();
    }
}