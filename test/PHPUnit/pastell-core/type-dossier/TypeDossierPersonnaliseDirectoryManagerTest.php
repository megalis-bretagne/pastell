<?php

class TypeDossierPersonnaliseDirectoryManagerTest extends PastellTestCase
{

    /**
     * @throws TypeDossierException
     * @throws UnrecoverableException
     */
    public function testDelete()
    {
        $typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
        $id_t = $typeDossierImportExport->importFromFilePath(__DIR__ . "/fixtures/cas-nominal.json")['id_t'];
        $this->assertFileExists($this->getWorkspacePath() . "/type-dossier-personnalise/module/cas-nominal/definition.yml");
        $typeDossierPersonaliseDirectoryManager = $this->getObjectInstancier()->getInstance(TypeDossierPersonnaliseDirectoryManager::class);
        $typeDossierPersonaliseDirectoryManager->delete($id_t);
        $this->assertFileNotExists($this->getWorkspacePath() . "/type-dossier-personnalise/module/cas-nominal/definition.yml");
    }

    /**
     * @throws UnrecoverableException
     */
    public function testDeleteNotExistingModule()
    {
        $typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
        $id_t = $typeDossierImportExport->importFromFilePath(__DIR__ . "/fixtures/cas-nominal.json")['id_t'];
        $this->assertFileExists($this->getWorkspacePath() . "/type-dossier-personnalise/module/cas-nominal/definition.yml");
        $typeDossierPersonaliseDirectoryManager = $this->getObjectInstancier()->getInstance(TypeDossierPersonnaliseDirectoryManager::class);
        try {
            $typeDossierPersonaliseDirectoryManager->delete($id_t + 42);
            $this->assertFalse(true);
        } catch (TypeDossierException $e) {
            $this->assertEquals("Impossible de trouver l'emplacement du type de dossier 43", $e->getMessage());
        }
        $this->assertFileExists($this->getWorkspacePath() . "/type-dossier-personnalise/module/cas-nominal/definition.yml");
    }

    /**
     * @throws TypeDossierException
     * @throws UnrecoverableException
     */
    public function testRename()
    {
        $typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
        $typeDossierImportExport->importFromFilePath(__DIR__ . '/fixtures/cas-nominal.json');
        $this->assertFileExists($this->getWorkspacePath() . '/type-dossier-personnalise/module/cas-nominal/definition.yml');
        $typeDossierPersonaliseDirectoryManager = $this->getObjectInstancier()->getInstance(TypeDossierPersonnaliseDirectoryManager::class);

        $typeDossierPersonaliseDirectoryManager->rename('cas-nominal', 'cas-nominal-new');
        $this->assertFileExists($this->getWorkspacePath() . '/type-dossier-personnalise/module/cas-nominal-new/definition.yml');
        $this->assertFileNotExists($this->getWorkspacePath() . '/type-dossier-personnalise/module/cas-nominal/definition.yml');
    }

    /**
     * @throws TypeDossierException
     * @throws UnrecoverableException
     */
    public function testRenameTargetTypeDossierAlreadyUsed()
    {
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("L'emplacement du type de dossier « cas-nominal-new » est déjà utilisé.");
        $typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
        $typeDossierImportExport->importFromFilePath(__DIR__ . '/fixtures/cas-nominal.json');
        $this->assertFileExists($this->getWorkspacePath() . '/type-dossier-personnalise/module/cas-nominal/definition.yml');
        mkdir($this->getWorkspacePath() . '/type-dossier-personnalise/module/cas-nominal-new');

        $typeDossierPersonaliseDirectoryManager = $this->getObjectInstancier()->getInstance(TypeDossierPersonnaliseDirectoryManager::class);
        $typeDossierPersonaliseDirectoryManager->rename('cas-nominal', 'cas-nominal-new');
    }

    private function getWorkspacePath()
    {
        return $this->getObjectInstancier()->getInstance('workspacePath');
    }
}
