<?php

class OrientationTypeDossierPersonaliseTest extends PastellTestCase {

    private $tmp_folder;

    /**
     * @throws Exception
     */
    public function setUp(){
        parent::setUp();
        $tmpFolder = new TmpFolder();
        $this->tmp_folder = $tmpFolder->create();
    }

    public function tearDown() {
        parent::tearDown();
        $tmpFolder = new TmpFolder();
        $tmpFolder->delete($this->tmp_folder);
        $this->getObjectInstancier()->getInstance(MemoryCache::class)->flushAll();
    }

    /**
     * @throws Exception
     */
    public function testCasNominal(){
        $this->doHorribleStuffToEnableTypeDossierCasNominal();

        $roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $roleSQL->addDroit('admin',"cas-nominal:lecture");
        $roleSQL->addDroit('admin',"cas-nominal:edition");

        $info = $this->createDocument("cas-nominal");

        $id_d = $info['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->addFileFromData(
            "arrete","arrete.pdf","aaa"
        );
        $info = $this->getInternalAPI()->patch("/Entite/1/document/$id_d", [
            "objet"=>'test',
            "prenom_agent"=>"eric",
            "nom_agent"=>"foo",
            "iparapheur_sous_type"=>"TEST"
        ]);

        $this->assertEquals(1,$info['formulaire_ok']);

        $result = $this->triggerActionOnDocument($id_d,"orientation",self::ID_E_COL,self::ID_U_ADMIN);
        $this->assertTrue($result);

        $info = $this->getInternalAPI()->get("/Entite/1/document/$id_d");
        $this->assertEquals('preparation-send-iparapheur',$info['last_action']['action']);
    }

    private function getWorkspacePath(){
        return $this->getObjectInstancier()->getInstance('workspacePath');
    }

    /**
     * La fonction glob() permet pas de rechercher dans le VFS, du coup, la génération dynamique
     * des fichiers de definition YAML n'est pas opérante à travers DocumentTypeFactory...
     *
     * Contournement : on réécrit le fichier quelque part et on charge le module...
     *
     * @throws Exception
     */
    private function doHorribleStuffToEnableTypeDossierCasNominal(){
        $typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);

        $id_t = $typeDossierSQL->edit(0,"cas-nominal");

        copy(
            __DIR__."/../pastell-core/type-dossier/fixtures/type_dossier_cas_nominal.json",
            $this->getWorkspacePath()."/type_dossier_$id_t.json"
        );
        $this->getObjectInstancier()
            ->getInstance(TypeDossierDefinition::class)
            ->reGenerate($id_t);

        mkdir($this->tmp_folder."/module/cas-nominal/",0777,true);
        copy($this->getWorkspacePath()."/".TypeDossierPersonnaliseDirectoryManager::SUB_DIRECTORY."/module/cas-nominal/definition.yml",
            $this->tmp_folder."/module/cas-nominal/definition.yml");

        $this->loadExtension(array($this->tmp_folder));
    }

}
