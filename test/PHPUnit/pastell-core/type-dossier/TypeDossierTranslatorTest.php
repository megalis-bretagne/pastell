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

    public function testTranslate(){

        $this->copyTypeDossierTest();
        $this->getTypeDossierDefinition()->sortEtape(3,[0,1,2]);

        $systemControler = $this->getObjectInstancier()->getInstance('SystemControler');
        $this->assertTrue($systemControler->isDocumentTypeValidByDefinitionPath(
            $this->getWorkspacePath()."/type-dossier-personnalise/module/definition.yml"
        ));



    }
}