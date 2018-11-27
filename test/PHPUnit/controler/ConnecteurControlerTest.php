<?php

class ConnecteurControlerTest extends ControlerTestCase {

	/**
	 * @var ConnecteurControler
	 */
	private $connecteurControler;

	protected function setUp(){
		parent::setUp();
		$this->connecteurControler = $this->getControlerInstance("ConnecteurControler");
	}

	public function testEditionActionConnecteurDoesNotExists(){
		$this->setExpectedException("Exception","Ce connecteur n'existe pas");
		$this->connecteurControler->editionAction();
	}

	public function testEditionAction(){
		$_GET['id_ce'] = 11;
		$this->expectOutputRegex("#Connecteur mailsec - mailsec : Mail securise#");
		$this->connecteurControler->editionAction();
	}

	public function testEditionLibelleAction(){
		$_GET['id_ce'] = 11;
		$this->expectOutputRegex("#Connecteur mailsec - mailsec : Mail securise#");
		$this->connecteurControler->editionLibelleAction();
	}
	
	public function testDoEditionLibelleFailed(){
		$this->setExpectedException("LastErrorException","Ce connecteur n'existe pas");
		$this->connecteurControler->doEditionLibelleAction();
	}


}