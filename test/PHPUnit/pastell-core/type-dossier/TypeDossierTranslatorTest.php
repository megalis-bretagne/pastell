<?php

class TypeDossierTranslatorTest extends PastellTestCase {

    const TYPE_DOSSIER_ID = 42;

    public function caseProvider(){
        return [
            ['cas_nominal'],
            ['ged_only'],
            ['sae_only'],
			['double_ged'],
			['parapheur_only'],
			['mailsec_only'],
			['tdt_actes_only'],
			['tdt_helios_only'],
			['double_parapheur']
        ];
    }

    /**
     * @dataProvider caseProvider
     * @param string $case
     * @throws Exception
     */
    public function testTranslation($case){
        $this->loadDossierType("type_dossier_$case.json");
        $this->validateDefinitionFile($case);
        $this->assertFileEquals(
            __DIR__."/fixtures/type_dossier_$case.yml",
            $this->getWorkspacePath()."/type-dossier-personnalise/module/$case/definition.yml"
        );
    }

    /**
	 *
     * @throws Exception
     */
    public function testTranslate(){
    	$type_dossier = 'cas_nominal';
        $this->loadDossierType("type_dossier_{$type_dossier}.json");
        $this->validateDefinitionFile($type_dossier);
        //file_put_contents(__DIR__."/fixtures/type_dossier_{$type_dossier}.yml",file_get_contents($this->getWorkspacePath()."/type-dossier-personnalise/module/definition.yml"));
        $this->assertFileEquals(
            __DIR__."/fixtures/type_dossier_{$type_dossier}.yml",
            $this->getWorkspacePath()."/type-dossier-personnalise/module/$type_dossier/definition.yml"
        );
    }

    private function getTypeDossierService(){
        return $this->getObjectInstancier()->getInstance(TypeDossierService::class);
    }

    private function getWorkspacePath(){
        return $this->getObjectInstancier()->getInstance('workspacePath');
    }

    /**
     * @param $type_dossier_definition_filename
     * @throws Exception
     */
    private function loadDossierType($type_dossier_definition_filename){
    	$typeDossierProperties = $this->getTypeDossierService()->getTypeDossierFromArray(json_decode(file_get_contents(__DIR__."/fixtures/$type_dossier_definition_filename"),true));
    	$id_t = $this->getTypeDossierService()->create($typeDossierProperties->id_type_dossier);
    	$this->getTypeDossierService()->save($id_t,$typeDossierProperties);
    }

    /**
     * @throws Exception
     */
    private function validateDefinitionFile($type_dossier){
        $systemControler = $this->getObjectInstancier()->getInstance('SystemControler');

        try {
            $validation_result = $systemControler->isDocumentTypeValidByDefinitionPath(
                $this->getWorkspacePath() . "/type-dossier-personnalise/module/$type_dossier/definition.yml"
            );
        } catch (Exception $e){
            echo file_get_contents($this->getWorkspacePath() . "/type-dossier-personnalise/module/$type_dossier/definition.yml");
            throw $e;
        }

        $this->assertTrue($validation_result);
    }

    /**
     * @throws Exception
     */
    public function testTranslationSameTypeOptionalStep(){
        $this->loadDossierType("double_parapheur_optional_step.json");
        $this->validateDefinitionFile("double_parapheur_optional_step");

        $ymlLoader = new YMLLoader(new MemoryCacheNone());
        $result = $ymlLoader->getArray($this->getWorkspacePath()."/type-dossier-personnalise/module/double_parapheur_optional_step/definition.yml");
        $this->assertEquals(array (
            'envoi_signature_1' =>
                array (
                    'name' => 'Visa/Signature #1',
                    'type' => 'checkbox',
                    'onchange' => 'cheminement-change',
                    'default' => '',
                    'read-only' => false,
                ),
            'envoi_signature_2' =>
                array (
                    'name' => 'Visa/Signature #2',
                    'type' => 'checkbox',
                    'onchange' => 'cheminement-change',
                    'default' => '',
                    'read-only' => false,
                ),
        ),$result['formulaire']['Cheminement']);

        $this->assertEquals(array (
            'i-Parapheur #1' =>
                array (
                    'envoi_signature_1' => true,
                ),
            'Signature #1' =>
                array (
                    'has_signature_1' => true,
                ),
            'i-Parapheur #2' =>
                array (
                    'envoi_signature_2' => true,
                ),
            'Signature #2' =>
                array (
                    'has_signature_2' => true,
                ),
        ),$result['page-condition']);


    }
}