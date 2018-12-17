<?php

class DocumentControlerTest extends ControlerTestCase {

	/**
	 * @throws Exception
	 */
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



    public function testActionActionNoRight(){
		$info = $this->getInternalAPI()->post("entite/1/document",array('type'=>'test'));

		$authentification = $this->getObjectInstancier()->getInstance("Authentification");
		$authentification->connexion('foo',42);

		/** @var DocumentControler $documentController */
		$documentController = $this->getObjectInstancier()->getInstance("DocumentControler");
		try {
			$this->expectOutputRegex("#id_e=1#");
			$documentController->setGetInfo(new Recuperateur(
				[
					'id_e' => 1,
					'id_d'=> $info['id_d'],
					'action'=> 'no-way'
				]
			));
			$documentController->actionAction();
		} catch (Exception $e){}
		$this->assertEquals(
			"Vous n'avez pas les droits nécessaires (1:test:edition) pour accéder à cette page",
			$documentController->getLastError()->getLastMessage()
			);
	}

	public function testActionAction(){
		$info = $this->getInternalAPI()->post("entite/1/document",array('type'=>'test'));

		$authentification = $this->getObjectInstancier()->getInstance("Authentification");
		$authentification->connexion('foo',1);


		/** @var DocumentControler $documentController */
		$documentController = $this->getObjectInstancier()->getInstance("DocumentControler");
		try {
			$this->expectOutputRegex("#id_e=1#");
			$documentController->setGetInfo(new Recuperateur(
				[
					'id_e' => 1,
					'id_d'=> $info['id_d'],
					'action'=> 'no-way'
				]
			));
			$documentController->actionAction();
		} catch (Exception $e){}
		$this->assertEquals(
			"L'action no-way a été executée sur le document",
			$documentController->getLastMessage()->getLastMessage()
		);
	}

	public function testTextareaReadOnly(){
		$info = $this->getInternalAPI()->post("entite/1/document",array('type'=>'test'));

		/** @var DocumentControler $documentControler */
		$documentControler = $this->getControlerInstance(DocumentControler::class);

		$this->setGetInfo(['id_e'=>1,'id_d'=>$info['id_d']]);

		$this->setOutputCallback(function($output){
			$this->assertEquals(0,
				preg_match("#<textarea .*  name='test_textarea' #",$output)
			);

			$this->assertEquals(1,
				preg_match("#<textarea .*  name='test_textarea_read_write' #",$output)
			);


		});
		$documentControler->editionAction();
	}

    /**
     *
     */
	public function testDownloadAllAction(){
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        /* ZipArchive ca marche pas avec le workspace émulé en mémoire */
        $this->getObjectInstancier()->setInstance('workspacePath',$tmp_folder);

        $info = $this->getInternalAPI()->post("entite/1/document",array('type'=>'actes-generique'));
        $id_d = $info['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->addFileFromCopy(
            'autre_document_attache',
            "vide.pdf",
            __DIR__."/../fixtures/vide.pdf",
            0
        );
        $donneesFormulaire->addFileFromCopy(
            'autre_document_attache',
            __DIR__."/../fixtures/vide.pdf",
            __DIR__."/../fixtures/test_extract_zip_structure/7756W3_9/7756_Bordereau_versement.pdf")
        ;

        /** @var DocumentControler $documentControler */
        $documentControler = $this->getControlerInstance(DocumentControler::class);

        $this->setGetInfo(['id_e'=>1,'id_d'=>$info['id_d'],'field'=>'autre_document_attache']);

        $this->expectOutputRegex("#Content-disposition: attachment; filename=\"fichier-1-$id_d-autre_document_attache.zip\"#");
        $documentControler->downloadAllAction();
        $tmpFolder->delete($tmp_folder);
    }

}