<?php

class OrientationTypeDossierPersonaliseTest extends PastellTestCase {

    /**
     * @throws Exception
     */
    public function testCasNominal(){
        $typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
        $id_t = $typeDossierSQL->edit(0,"cas-nominal");

        copy(
            __DIR__."/../pastell-core/type-dossier/fixtures/type_dossier_cas_nominal.json",
            $this->getWorkspacePath()."/type_dossier_$id_t.json"
        );
        $this->getObjectInstancier()
            ->getInstance(TypeDossierDefinition::class)
            ->reGenerate($id_t);

        $documentTypeFactory = $this->getObjectInstancier()->getInstance(DocumentTypeFactory::class);

        $roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $roleSQL->addDroit('admin',"cas-nominal:lecture");
        $roleSQL->addDroit('admin',"cas-nominal:edition");

        $info = $this->createDocument("cas-nominal");

        $this->assertNotEmpty($info);

        //TODO to be continued...
        // => a cause du glob() sur la recherche des types de document, on peut pas tester de cette manière...
        // => solution : ajouter le fixtures dans la zone de recherche des modules ?  

        /*$info = $this->getInternalAPI()->patch("/Entite/1/document/{$info['id_d']}",
            ["objet"=>'test']);

        print_r($info);
        $result = $this->triggerActionOnDocument($info['id_d'],"orientation",self::ID_E_COL,self::ID_U_ADMIN);

        $this->assertTrue($result);*/


    }

    private function getWorkspacePath(){
        return $this->getObjectInstancier()->getInstance('workspacePath');
    }

}