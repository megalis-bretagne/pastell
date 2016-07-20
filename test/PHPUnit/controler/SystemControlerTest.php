<?php 

class SystemControlerTest extends ControlerTestCase {

	/** @var  SystemControler */
	private $systemControler;

	public function setUp(){
		parent::setUp();
		$this->systemControler = $this->getControlerInstance("SystemControler");
	}
	
	public function testDoExtensionEditionAction() {
		//$_POST['path'] = '/tmp/';
		$this->setExpectedException("Exception");
		$this->systemControler->doExtensionEditionAction();
	}

	public function testDoExtensionEditionActionFail() {
		//$_POST['path'] = '';
		$this->setExpectedException("LastErrorException");
		$this->systemControler->doExtensionEditionAction();
	}

	public function testFluxDetailAction(){
		//$_GET['id'] = 'actes-generique';
		$this->expectOutputRegex("##");
		$this->systemControler->fluxDetailAction();
	}
	
	public function testIndex() {
		$this->expectOutputRegex("##");
		$this->systemControler->indexAction();
	}
	
	
	
	
}