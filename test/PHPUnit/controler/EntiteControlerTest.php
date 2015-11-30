<?php

class EntiteControlerTest extends PastellTestCase {

	/**
	 * @var EntiteControler
	 */
	private $entiteControler;

	protected  function setUp(){
		parent::setUp();
		$this->getObjectInstancier()->Authentification->Connexion('admin',1);
		$this->entiteControler = new EntiteControler($this->getObjectInstancier());
		$this->entiteControler->setDontRedirect(true);
	}

	public function reinitDatabaseOnSetup(){
		return true;
	}

	public function reinitFileSystemOnSetup(){
		return true;
	}

	public function testListConnecteur(){
		$this->entiteControler->listConnecteur();
		$all_connecteur = $this->entiteControler->getViewParameter("all_connecteur");
		$this->assertEquals("horodateur-interne",$all_connecteur['all_connecteur'][0]['id_connecteur']);
	}

}