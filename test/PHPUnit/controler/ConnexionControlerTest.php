<?php

class ConnexionControlerTest extends ControlerTestCase {

	/**
	 * @var ConnexionControler
	 */
	private $connexionControler;

	protected function setUp(){
		parent::setUp();
		$this->connexionControler = $this->getControlerInstance("ConnexionControler");
	}

	public function testNotConnected() {
		$this->setExpectedException("LastMessageException");
		$this->getObjectInstancier()->Authentification->deconnexion();
		$this->connexionControler->verifConnected();
	}
	
	public function testConnexion(){
		$this->getObjectInstancier()->Authentification->Connexion('admin',1);
		$this->assertTrue($this->connexionControler->verifConnected());
	}
	
	public function testConnexionAction(){
		$this->expectOutputRegex("#Veuillez saisir vos identifiants de connexion#");
		$this->connexionControler->connexionAction();
	}
	
	public function testConnexionAdminAction(){
		$this->expectOutputRegex("#Veuillez saisir vos identifiants de connexion#");
		$this->connexionControler->adminAction();
	}
	
	public function testOublieIdentifiant(){
		$this->expectOutputRegex("##");
		$this->connexionControler->oublieIdentifiantAction();
	}

	public function testChangementMdpAction(){
		$this->expectOutputRegex("##");
		$this->connexionControler->changementMdpAction();
	}
	
	public function testChangementNoDroitAction(){
		$this->expectOutputRegex("##");
		$this->connexionControler->noDroitAction();
	}
	
	public function testCasErrorAction(){
		$this->expectOutputRegex("##");
		$this->connexionControler->casErrorAction();
	}

	public function testLogoutAction(){
		$this->setExpectedException("LastMessageException");
		$this->connexionControler->logoutAction();
	}
}