<?php

use Pastell\Service\TypeDossier\TypeDossierImportService;

class TypeDossierTranslatorTest extends PastellTestCase
{
    public const TYPE_DOSSIER_ID = 42;

    public function caseProvider(): iterable
    {
        yield 'cas-nominal' => ['cas-nominal'];
        yield 'double-ged' => ['double-ged'];
        yield 'ged-only' => ['ged-only'];
        yield 'mailsec-only' => ['mailsec-only'];
        yield 'sae-only' => ['sae-only'];
        yield 'tdt-actes-only' => ['tdt-actes-only'];
        yield 'tdt-helios-only' => ['tdt-helios-only'];
        yield 'parapheur-only' => ['parapheur-only'];
        yield 'double-parapheur' => ['double-parapheur'];
        yield 'test-select' => ['test-select'];
        yield 'test-regex' => ['test-regex'];
        yield 'redefined-field' => ['redefined-field'];
        yield 'tdt-actes-with-specific-righ' => ['tdt-actes-with-specific-right'];
        yield 'double-transformation' => ['double-transformation'];
        yield 'studio-transformation' => ['studio-transformation'];
    }
    /**
     * @dataProvider caseProvider
     * @param string $case
     * @throws Exception
     */
    public function testTranslation(string $case)
    {
        $this->loadDossierType("$case.json");
        $this->validateDefinitionFile($case);

//        file_put_contents(__DIR__ . "/fixtures/$case.yml", file_get_contents($this->getWorkspacePath() . "/type-dossier-personnalise/module/$case/definition.yml"));
        $this->assertFileEquals(
            __DIR__ . "/fixtures/$case.yml",
            $this->getWorkspacePath() . "/type-dossier-personnalise/module/$case/definition.yml"
        );
    }

    /**
     *
     * @throws Exception
     */
    public function testTranslate()
    {
        $type_dossier = 'cas-nominal';
        $this->loadDossierType("{$type_dossier}.json");
        $this->validateDefinitionFile($type_dossier);
         //file_put_contents(__DIR__ . "/fixtures/{$type_dossier}.yml", file_get_contents($this->getWorkspacePath() . "/type-dossier-personnalise/module/$type_dossier/definition.yml"));
        $this->assertFileEquals(
            __DIR__ . "/fixtures/{$type_dossier}.yml",
            $this->getWorkspacePath() . "/type-dossier-personnalise/module/$type_dossier/definition.yml"
        );
    }

    private function getWorkspacePath()
    {
        return $this->getObjectInstancier()->getInstance('workspacePath');
    }

    /**
     * @param $type_dossier_definition_filename
     * @throws UnrecoverableException
     * @throws TypeDossierException
     */
    private function loadDossierType($type_dossier_definition_filename)
    {
        $typeDossierImportService = $this->getObjectInstancier()->getInstance(TypeDossierImportService::class);
        $typeDossierImportService->importFromFilePath(__DIR__ . "/fixtures/$type_dossier_definition_filename");
    }

    /**
     * @param $type_dossier
     * @throws Exception
     */
    private function validateDefinitionFile($type_dossier)
    {
        $systemControler = $this->getObjectInstancier()->getInstance(SystemControler::class);

        try {
            $validation_result = $systemControler->isDocumentTypeValidByDefinitionPath(
                $this->getWorkspacePath() . "/type-dossier-personnalise/module/$type_dossier/definition.yml"
            );
        } catch (Exception $e) {
            echo file_get_contents($this->getWorkspacePath() . "/type-dossier-personnalise/module/$type_dossier/definition.yml");
            throw $e;
        }

        $this->assertTrue($validation_result);
    }

    /**
     * @throws TypeDossierException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testTranslationSameTypeOptionalStep()
    {
        $this->loadDossierType("double-parapheur-optional-step.json");
        $this->validateDefinitionFile("double-parapheur-optional-step");

        $ymlLoader = new YMLLoader(new MemoryCacheNone());
        $result = $ymlLoader->getArray($this->getWorkspacePath() . "/type-dossier-personnalise/module/double-parapheur-optional-step/definition.yml");
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
        ), $result['formulaire']['Cheminement']);

        $this->assertEquals(array (
            'i-Parapheur #1' =>
                array (
                    'envoi_iparapheur_1' => true,
                ),
            'Signature #1' =>
                array (
                    'has_signature_1' => true,
                ),
            'i-Parapheur #2' =>
                array (
                    'envoi_iparapheur_2' => true,
                ),
            'Signature #2' =>
                array (
                    'has_signature_2' => true,
                ),
            'Parapheur FAST #1' => [
                'envoi_fast_1' => true,
            ],
            'Parapheur FAST #2' => [
                'envoi_fast_2' => true,
            ],
        ), $result['page-condition']);
    }

    /**
     * @throws TypeDossierException
     * @throws UnrecoverableException
     */
    public function testRenamedStep(): void
    {
        $this->loadDossierType('ged-only-renamed-step.json');

        $ymlLoader = new YMLLoader(new MemoryCacheNone());
        $result = $ymlLoader->getArray($this->getWorkspacePath() . '/type-dossier-personnalise/module/ged-only-renamed-step/definition.yml');

        $this->assertSame('Renamed step', $result['formulaire']['Cheminement']['envoi_depot']['name']);
    }
}
