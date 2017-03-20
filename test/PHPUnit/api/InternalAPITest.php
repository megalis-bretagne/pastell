<?php

class InternalAPITest extends PastellTestCase {

	/** @var  InternalAPI */
	private $internalAPI;

	protected function setUp() {
		parent::setUp();
		$this->internalAPI = $this->getObjectInstancier()->getInstance('InternalAPI');
	}

	public function testGetVersion(){
		$this->internalAPI->setUtilisateurId(1);
		$version_info = $this->internalAPI->get("/version");
		$this->assertEquals("1.4-fixtures",$version_info['version']);
	}

	public function testUnauhtenticated(){
		$this->setExpectedException("UnauthorizedException","Vous devez être connecté pour utiliser l'API");
		$this->internalAPI->get("/version");
	}



}