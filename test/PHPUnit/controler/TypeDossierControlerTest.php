<?php

class TypeDossierControlerTest extends ControlerTestCase
{

    /**
     * @return TypeDossierControler
     */
    private function getTypeDossierController()
    {
        return $this->getControlerInstance(TypeDossierControler::class);
    }

    /**
     * @return int
     * @throws Exception
     */
    private function getTypeDossierId()
    {
        $this->getTypeDossierController();
        $typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
        $info = $typeDossierImportExport->importFromFilePath(
            __DIR__ . "/../pastell-core/type-dossier/fixtures/cas-nominal.json"
        );
        return $info['id_t'];
    }



    private function createTypeDossier($type_dossier_id): int
    {
        $typeDossierService = $this->getObjectInstancier()->getInstance(TypeDossierService::class);
        return $typeDossierService->create($type_dossier_id);
    }
    /**
     * @throws Exception
     */
    public function testExportAction()
    {
        $id_t = $this->getTypeDossierId();
        $typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
        $typeDossierImportExport->setTimeFunction(function () {
            return "42";
        });
        $this->setGetInfo(['id_t' => $id_t]);
        $this->expectOutputRegex("#cas-nominal.json#");
        $this->getTypeDossierController()->exportAction();
    }

    /**
     * @throws Exception
     */
    public function testDoDeleteAction()
    {

        $typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
        $typeDossierPersonnaliseDirectoryManager = $this->getObjectInstancier()->getInstance(TypeDossierPersonnaliseDirectoryManager::class);

        $id_t = $this->getTypeDossierId();
        $this->assertTrue($typeDossierSQL->exists($id_t));
        $this->assertFileExists($typeDossierPersonnaliseDirectoryManager->getTypeDossierPath($id_t));
        $type_dossier_path = $typeDossierPersonnaliseDirectoryManager->getTypeDossierPath($id_t);
        $this->setGetInfo(['id_t' => $id_t]);
        try {
            $this->getTypeDossierController()->doDeleteAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegexp("#Le type de dossier <b>cas-nominal</b> a été supprimé#", $e->getMessage());
        }
        $this->assertFalse($typeDossierSQL->exists($id_t));
        $this->assertFileNotExists($type_dossier_path);
    }

    /**
     * @throws Exception
     */
    public function testDoDeleteActionWhenTypeDossierIsUsed()
    {
        $typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
        $typeDossierPersonnaliseDirectoryManager = $this->getObjectInstancier()->getInstance(TypeDossierPersonnaliseDirectoryManager::class);

        $id_t = $this->getTypeDossierId();

        $this->getObjectInstancier()->getInstance(RoleSQL::class)->addDroit('admin', "cas-nominal:lecture");
        $this->getObjectInstancier()->getInstance(RoleSQL::class)->addDroit('admin', "cas-nominal:edition");
        $this->getObjectInstancier()->getInstance(RoleUtilisateur::class)->deleteCache(1, 1);

        $this->createDocument('cas-nominal');

        $this->assertTrue($typeDossierSQL->exists($id_t));
        $this->assertFileExists($typeDossierPersonnaliseDirectoryManager->getTypeDossierPath($id_t));

        $this->setGetInfo(['id_t' => $id_t]);
        try {
            $this->getTypeDossierController()->doDeleteAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegexp("#Le type de dossier cas-nominal est utilisé par des documents présents dans la base de données.#", $e->getMessage());
        }
        $this->assertTrue($typeDossierSQL->exists($id_t));
        $this->assertFileExists($typeDossierPersonnaliseDirectoryManager->getTypeDossierPath($id_t));
    }

    /**
     * @throws Exception
     */
    public function testDoEditionAction()
    {
        $id_type_dossier = 'test-52';
        $this->getTypeDossierController();
        $this->setGetInfo(['id_type_dossier' => $id_type_dossier]);
        try {
            $this->getTypeDossierController()->doEditionAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegExp("#Le type de dossier personnalisé $id_type_dossier a été créé#", $e->getMessage());
        }

        $typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
        $id_t = $typeDossierSQL->getByIdTypeDossier($id_type_dossier);
        $this->assertEquals($id_type_dossier, $typeDossierSQL->getInfo($id_t)['id_type_dossier']);
    }

    /**
     * @throws Exception
     */
    public function testDoEditionActionWhenIdTypeDossierIsNull()
    {
        try {
            $this->getTypeDossierController()->doEditionAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegExp("#Aucun identifiant de type de dossier fourni#", $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function testDoEditionActionWhenIdTypeDossierDoesNotMatchRegexp()
    {
        $this->getTypeDossierController();
        $this->setGetInfo(['id_type_dossier' => 'AAAAA']);
        try {
            $this->getTypeDossierController()->doEditionAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegExp(
                "#L'identifiant du type de dossier « AAAAA » ne respecte pas l'expression rationnelle#u",
                $e->getMessage()
            );
        }
    }
    /**
     * @throws Exception
     */
    public function testDoEditionActionWhenIdTypeDossierOverflowMaxLength()
    {
        $this->getTypeDossierController();
        $this->setGetInfo(['id_type_dossier' => str_repeat("a", 33)]);
        try {
            $this->getTypeDossierController()->doEditionAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegExp(
                "#L'identifiant du type de dossier « aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa » ne doit pas dépasser 32 caractères#u",
                $e->getMessage()
            );
        }
    }

    public function testDoNewEtapeAction()
    {
        $this->getTypeDossierController();
        $typeDossierService = $this->getObjectInstancier()->getInstance(TypeDossierService::class);
        $id_t = $typeDossierService->create('test-42');
        $this->setGetInfo(['id_t' => $id_t,'type' => 'signature']);

        try {
            $this->getTypeDossierController()->doNewEtapeAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegExp("#/TypeDossier/editionEtape\?id_t=$id_t&num_etape=0#", $e->getMessage());
        }
    }

    public function testDoNewEtapeActionNoSpecificData()
    {
        $this->getTypeDossierController();
        $id_t = $this->createTypeDossier('test-42');
        $this->setGetInfo(['id_t' => $id_t,'type' => 'depot']);

        try {
            $this->getTypeDossierController()->doNewEtapeAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegExp("#/TypeDossier/detail\?id_t=$id_t#", $e->getMessage());
        }
    }

    public function testDelete()
    {
        $typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);

        $this->getTypeDossierController();
        $id_t = $this->createTypeDossier('test-42');
        $this->assertTrue($typeDossierSQL->exists($id_t));
        $this->setGetInfo(['id_t' => $id_t]);
        try {
            $this->getTypeDossierController()->doDeleteAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegExp("#Le type de dossier <b>test-42</b> a été supprimé#", $e->getMessage());
        }
        $this->assertFalse($typeDossierSQL->exists($id_t));
    }

    public function testDeleteWhenUsedByDroit()
    {
        $typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);

        $this->getTypeDossierController();
        $id_t = $this->createTypeDossier('test-42');
        $this->assertTrue($typeDossierSQL->exists($id_t));

        $this->getObjectInstancier()->getInstance(RoleSQL::class)->addDroit('admin', "test-42:lecture");
        $this->getObjectInstancier()->getInstance(RoleSQL::class)->addDroit('admin', "test-42:edition");

        $this->setGetInfo(['id_t' => $id_t]);
        try {
            $this->getTypeDossierController()->doDeleteAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegExp(
                "#Le type de dossier <b>test-42</b> est utilisé par le rôle « admin »#",
                $e->getMessage()
            );
        }
        $this->assertTrue($typeDossierSQL->exists($id_t));
    }

    public function testDeleteWhenConnecteurIsAssociatedWithTypeDossier()
    {
        $typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);

        $this->getTypeDossierController();
        $id_t = $this->createTypeDossier('test-42');

        $fluxEntiteSQL = $this->getObjectInstancier()->getInstance(FluxEntiteSQL::class);

        $fluxEntiteSQL->addConnecteur(1, 'test-42', 'GED', 42);

        $this->setGetInfo(['id_t' => $id_t]);
        try {
            $this->getTypeDossierController()->doDeleteAction();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertRegExp(
                "#Le type de dossier <b>test-42</b> a été associé avec des connecteurs sur l'entité Bourg-en-Bresse \(id_e=1\)#",
                $e->getMessage()
            );
        }
        $this->assertTrue($typeDossierSQL->exists($id_t));
    }
}
