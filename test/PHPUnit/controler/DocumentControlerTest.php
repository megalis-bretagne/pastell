<?php

class DocumentControlerTest extends PastellTestCase {

    public function testReindex(){

        $info = $this->getInternalAPI()->post("entite/1/document",array('type'=>'test'));

        $this->getInternalAPI()->patch(
            "entite/1/document/{$info['id_d']}",
            array('nom'=>'foo')
        );

        $result = $this->getInternalAPI()->get("entite/1/document?type=test&nom=foo");
        $this->assertEquals($info['id_d'],$result[0]['id_d']);

        $this->getSQLQuery()->query("DELETE FROM document_index");
        $result = $this->getInternalAPI()->get("entite/1/document?type=test&nom=foo");
        $this->assertEmpty($result);

        /** @var DocumentControler $documentController */
        $documentController = $this->getObjectInstancier()->getInstance("DocumentControler");
        $this->expectOutputString(
            "Nombre de documents : 1\nRéindexation du document  ({$info['id_d']})\n"
        );
        $documentController->reindex('test','nom');
        $result = $this->getInternalAPI()->get("entite/1/document?type=test&nom=foo");
        $this->assertEquals($info['id_d'],$result[0]['id_d']);
    }


}