<?php

class TypeDossierDefintionTest extends PastellTestCase {

	private function getTypeDossierDefinition(){
		return $this->getObjectInstancier()->getInstance(TypeDossierDefinition::class);
	}

	/**
	 * @throws Exception
	 */
	public function testGetEmptyDossierData(){
		$typeDossierData42 = $this->getTypeDossierDefinition()->getTypeDossierData(42);
		$typeDossierData = new TypeDossierData();
		$this->assertEquals($typeDossierData,$typeDossierData42);
	}

	/**
	 * @throws Exception
	 */
	public function testEditLibelleInfo(){
		$this->getTypeDossierDefinition()->editLibelleInfo(
			41,
			"arrete-rh",
			"Flux CD 99",
			"Ceci est un flux de test",
			"Information"
		);

		$recuperateur = new Recuperateur([
			'element_id'=>1,
			'name'=>'nom_agent',
			'type' => 'text',
			'requis'=>'true'
		]);


		//$this->getTypeDossierDefinition()->editionElement(41,$recuperateur);

		$this->assertEquals(
			'{"nom":"arrete-rh","type":"Flux CD 99","description":"Ceci est un flux de test","nom_onglet":"Information","formulaireElement":[],"etape":[]}',
			file_get_contents($this->getObjectInstancier()->getInstance('workspacePath')."/type_dossier_41.json")
		);
	}



}