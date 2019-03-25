<?php

class TypeDossierTranslatorTest extends PastellTestCase {

    const TYPE_DOSSIER_ID = 42;

    public function caseProvider(){
        return [
            ['cas_nominal'],
            ['ged_only'],
            ['sae_only'],
        ];
    }

    /**
     * @dataProvider caseProvider
     * @throws Exception
     */
    public function testTranslation($case){
        $this->loadDossierType("type_dossier_$case.json");
        $this->validateDefinitionFile();
        $this->assertFileEquals(
            __DIR__."/fixtures/type_dossier_$case.yml",
            $this->getWorkspacePath()."/type-dossier-personnalise/module/definition.yml"
        );
    }

    /**
     * @throws Exception
     */
    public function testTranslate(){
        $this->loadDossierType("type_dossier_sae_only.json");
        $this->validateDefinitionFile();
        //file_put_contents(__DIR__."/fixtures/type_dossier_sae_only.yml",file_get_contents($this->getWorkspacePath()."/type-dossier-personnalise/module/definition.yml"));
        $this->assertFileEquals(
            __DIR__."/fixtures/type_dossier_sae_only.yml",
            $this->getWorkspacePath()."/type-dossier-personnalise/module/definition.yml"
        );
    }





    private function getTypeDossierDefinition(){
        return $this->getObjectInstancier()->getInstance(TypeDossierDefinition::class);
    }

    private function getWorkspacePath(){
        return $this->getObjectInstancier()->getInstance('workspacePath');
    }

    /**
     * @param $type_dossier_definition_filename
     * @throws Exception
     */
    private function loadDossierType($type_dossier_definition_filename){
        copy(
            __DIR__."/fixtures/$type_dossier_definition_filename",
            sprintf(
                "%s/type_dossier_%d.json",
                $this->getWorkspacePath(),
                self::TYPE_DOSSIER_ID
            )
        );
        $this->getTypeDossierDefinition()->reGenerate(self::TYPE_DOSSIER_ID);
    }

    /**
     * @throws Exception
     */
    private function validateDefinitionFile(){
        $systemControler = $this->getObjectInstancier()->getInstance('SystemControler');

        try {
            $validation_result = $systemControler->isDocumentTypeValidByDefinitionPath(
                $this->getWorkspacePath() . "/type-dossier-personnalise/module/definition.yml"
            );
        } catch (UnrecoverableException $e){
            echo file_get_contents($this->getWorkspacePath() . "/type-dossier-personnalise/module/definition.yml");
            throw $e;
        }

        $this->assertTrue($validation_result);
    }


}