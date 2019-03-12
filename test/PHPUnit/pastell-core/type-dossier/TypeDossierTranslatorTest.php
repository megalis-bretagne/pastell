<?php

class TypeDossierTranslatorTest extends PastellTestCase {

    private function getTypeDossierDefinition(){
        return $this->getObjectInstancier()->getInstance(TypeDossierDefinition::class);
    }

    private function getWorkspacePath(){
        return $this->getObjectInstancier()->getInstance('workspacePath');
    }

    private function copyTypeDossierTest(){
        copy(
            __DIR__."/fixtures/type_dossier_3.json",
            $this->getWorkspacePath()."/type_dossier_3.json"
        );
    }

	/**
	 * @throws Exception
	 */
    public function testTranslate(){

        $this->copyTypeDossierTest();
        $this->getTypeDossierDefinition()->sortEtape(3,[0,1,2]);

        $systemControler = $this->getObjectInstancier()->getInstance('SystemControler');
        $this->assertTrue($systemControler->isDocumentTypeValidByDefinitionPath(
            $this->getWorkspacePath()."/type-dossier-personnalise/module/definition.yml"
        ));

		$ymlLoader = $this->getObjectInstancier()->getInstance(YMLLoader::class);
		$array = $ymlLoader->getArray($this->getWorkspacePath()."/type-dossier-personnalise/module/definition.yml");

        $this->assertEquals(['creation','modification','importation','recu-iparapheur','reception','send-ged'],$array['action']['orientation']['rule']['last-action']);

        $this->assertFalse(isset($array['action']['reception']['action-automatique']));

        //file_put_contents(__DIR__."/fixtures/type_dossier_3_definition.yml",file_get_contents($this->getWorkspacePath()."/type-dossier-personnalise/module/definition.yml"));
		$this->assertFileEquals(
			__DIR__."/fixtures/type_dossier_3_definition.yml",
			$this->getWorkspacePath()."/type-dossier-personnalise/module/definition.yml"
		);
    }



}