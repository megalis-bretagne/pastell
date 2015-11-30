<?php

class ConnecteurControlerTest extends PastellTestCase {

	/**
	 * @var ConnecteurControler
	 */
	private $connecteurControler;

	protected function setUp(){
		parent::setUp();
		$this->connecteurControler = new ConnecteurControler($this->getObjectInstancier());
		$this->connecteurControler->setDontRedirect(true);
		$this->getObjectInstancier()->Authentification->Connexion('admin',1);
	}

	public function testEditionActionConnecteurDoesNotExists(){
		$this->setExpectedException("Exception","Ce connecteur n'existe pas");
		$this->connecteurControler->editionAction();
	}

	public function testEditionAction(){
		$_GET['id_ce'] = 11;
		$this->expectOutputRegex("#Connecteur mailsec - mailsec : Mail securise #");
		$this->connecteurControler->editionAction();
	}

}