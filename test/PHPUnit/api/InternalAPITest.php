<?php

class InternalAPITest extends PastellTestCase {

	public function testGetVersion(){
		$version_info = $this->getInternalAPI()->get("/version");
		$this->assertEquals("1.4-fixtures",$version_info['version']);
	}

	public function testUnauhtenticated(){
		$internalAPI = $this->getInternalAPI();
		$internalAPI->setUtilisateurId(false);
		$this->setExpectedException("UnauthorizedException","Vous devez être connecté pour utiliser l'API");
		$internalAPI->get("/version");
	}

	public function testScriptTest(){
		$internalAPI = $this->getInternalAPI();
		$internalAPI->setUtilisateurId(false);
		$internalAPI->setCallerType(InternalAPI::CALLER_TYPE_SCRIPT);
		$version_info = $internalAPI->get("/version");
		$this->assertEquals("1.4-fixtures",$version_info['version']);
	}

	public function testRessourceAbsente(){
		$this->setExpectedException("Exception","Ressource absente");
		$this->getInternalAPI()->get("");
	}

	public function testNotExistingRessource(){
		$this->setExpectedException("NotFoundException","La ressource foo n'a pas été trouvée");
		$this->getInternalAPI()->get("/foo");
	}
}