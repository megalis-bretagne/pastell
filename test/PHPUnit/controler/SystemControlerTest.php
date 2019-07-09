<?php

class SystemControlerTest extends ControlerTestCase {

	/** @var  SystemControler */
	private $systemControler;

	public function setUp(){
		parent::setUp();
		$this->systemControler = $this->getControlerInstance("SystemControler");
	}


	public function testFluxDetailAction(){
		$this->expectOutputRegex("##");
		$this->systemControler->fluxDetailAction();
	}

	public function testIndex() {
		$this->expectOutputRegex("#Test du système#");
		$this->systemControler->indexAction();
	}
}
