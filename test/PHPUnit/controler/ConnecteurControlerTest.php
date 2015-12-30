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

	public function testEditionLibelleAction(){
		$_GET['id_ce'] = 11;
		$this->expectOutputRegex("#Connecteur mailsec - mailsec : Mail securise#");
		$this->connecteurControler->editionLibelleAction();
	}

	public function testDoEditionLibelle(){
		$_POST['id_ce'] = 11;
		$_POST['libelle'] = "Test modification nom connecteur";
		$_POST['frequence_en_minute'] = 19;
		$_POST['id_verrou'] = 'VERROU';
		try {
			$this->connecteurControler->doEditionLibelle();
		} catch (LastMessageException $e){
			$this->assertRegExp("#Le connecteur « Test modification nom connecteur » a été modifié#",$e->getMessage());
		}
		$connecteur = $this->getConnecteurFactory()->getConnecteurById(11);
		$info = $connecteur->getConnecteurInfo();
		$this->assertEquals("VERROU",$info['id_verrou']);
	}

	public function testDoEditionLibelleFailed(){
		$this->setExpectedException("LastErrorException","Ce connecteur n'existe pas");
		$this->connecteurControler->doEditionLibelle();
	}


}