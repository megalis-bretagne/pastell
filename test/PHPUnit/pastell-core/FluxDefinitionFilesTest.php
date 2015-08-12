<?php
require_once __DIR__.'/../init.php';

class FluxDefinitionFilesTest extends PastellTestCase {
	
	public function reinitDatabaseOnSetup(){
		return true;
	}
	
	public function reinitFileSystemOnSetup(){
		return true;
	}
	
	/**
	 * @return FluxDefinitionFiles
	 */
	private function getFluxDefinitionFiles(){
		$ymlLoader = new YMLLoader();
		$fluxDefinitionFiles = new FluxDefinitionFiles($this->getObjectInstancier()->Extensions, $ymlLoader);
		return $fluxDefinitionFiles;
	}
	
	public function testGetAll(){
		$flux_list = $this->getFluxDefinitionFiles()->getAll();
		$element = array_shift($flux_list);
		$this->assertEquals("Authentification OpenID", $element['nom']);
	}
	
	public function testGetInfo(){
		$flux_info = $this->getFluxDefinitionFiles()->getInfo('mailsec');
		$this->assertEquals("Mail sécurisé", $flux_info['nom']);
	}
	
}
	