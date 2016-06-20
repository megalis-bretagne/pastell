<?php

class FluxDefinitionFilesTest extends PastellTestCase {

	/**
	 * @return FluxDefinitionFiles
	 */
	private function getFluxDefinitionFiles(){
		$ymlLoader = new YMLLoader(new MemoryCacheNone());
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
	