<?php

class TypeDossierTranslatorTest extends PastellTestCase {

    const TYPE_DOSSIER_ID = 42;

	public function caseProvider(){
		return [
			['cas-nominal'],
			['double-ged'],
			['ged-only'],
			['mailsec-only'],
			['sae-only'],
			['tdt-actes-only'],
			['tdt-helios-only'],
			['parapheur-only'],
			['double-parapheur']
		];
	}
	/**
	 * @dataProvider caseProvider
	 * @param string $case
	 * @throws Exception
	 */
	public function testTranslation($case){
		$this->loadDossierType("$case.json");
		$this->validateDefinitionFile($case);
		$this->assertFileEquals(
			__DIR__."/fixtures/$case.yml",
			$this->getWorkspacePath()."/type-dossier-personnalise/module/$case/definition.yml"
		);
	}

    /**
	 *
     * @throws Exception
     */
    public function testTranslate(){
        $type_dossier = 'cas-nominal';
        $this->loadDossierType("{$type_dossier}.json");
        $this->validateDefinitionFile($type_dossier);
        //file_put_contents(__DIR__ . "/fixtures/{$type_dossier}.yml", file_get_contents($this->getWorkspacePath() . "/type-dossier-personnalise/module/$type_dossier/definition.yml"));
        $this->assertFileEquals(
            __DIR__ . "/fixtures/{$type_dossier}.yml",
            $this->getWorkspacePath() . "/type-dossier-personnalise/module/$type_dossier/definition.yml"
        );
    }

    private function getWorkspacePath(){
        return $this->getObjectInstancier()->getInstance('workspacePath');
    }

	/**
	 * @param $type_dossier_definition_filename
	 * @throws UnrecoverableException
	 */
	private function loadDossierType($type_dossier_definition_filename){
    	$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$typeDossierImportExport->importFromFilePath(__DIR__."/fixtures/$type_dossier_definition_filename");
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
        $this->loadDossierType("double-parapheur-optional-step.json");
        $this->validateDefinitionFile("double-parapheur-optional-step");

        $ymlLoader = new YMLLoader(new MemoryCacheNone());
        $result = $ymlLoader->getArray($this->getWorkspacePath()."/type-dossier-personnalise/module/double-parapheur-optional-step/definition.yml");
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