<?php


class TypeDossierPersonnaliseDirectoryManagerTest extends PastellTestCase {

    /**
     * @throws TypeDossierException
     * @throws UnrecoverableException
     */
    public function testDelete(){
        $typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
        $id_t = $typeDossierImportExport->importFromFilePath(__DIR__."/fixtures/cas-nominal.json")['id_t'];
        $this->assertFileExists($this->getWorkspacePath()."/type-dossier-personnalise/module/cas-nominal/definition.yml");
        $typeDossierPersonaliseDirectoryManager = $this->getObjectInstancier()->getInstance(TypeDossierPersonnaliseDirectoryManager::class);
        $typeDossierPersonaliseDirectoryManager->delete($id_t);
        $this->assertFileNotExists($this->getWorkspacePath()."/type-dossier-personnalise/module/cas-nominal/definition.yml");
    }

    /**
     * @throws UnrecoverableException
     */
    public function testDeleteNotExistingModule(){
        $typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
        $id_t = $typeDossierImportExport->importFromFilePath(__DIR__."/fixtures/cas-nominal.json")['id_t'];
        $this->assertFileExists($this->getWorkspacePath()."/type-dossier-personnalise/module/cas-nominal/definition.yml");
        $typeDossierPersonaliseDirectoryManager = $this->getObjectInstancier()->getInstance(TypeDossierPersonnaliseDirectoryManager::class);
        try {
            $typeDossierPersonaliseDirectoryManager->delete($id_t + 42);
            $this->assertFalse(true);
        } catch (TypeDossierException $e){
            $this->assertEquals("Impossible de trouver l'emplacement du type de dossier 43",$e->getMessage());
        }
        $this->assertFileExists($this->getWorkspacePath()."/type-dossier-personnalise/module/cas-nominal/definition.yml");
    }

    private function getWorkspacePath(){
        return $this->getObjectInstancier()->getInstance('workspacePath');
    }
}