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

    public function testListDocument()
    {
        $this->expectOutputRegex('/Liste des dossiers Actes \(générique\) pour Bourg-en-Bresse/');
        $documentController = $this->getControlerInstance(DocumentControler::class);
        $this->setGetInfo([
            'id_e' => 1,
            'type' => 'actes-generique',
            'filtre' => 'modification',
        ]);

        $documentController->listAction();

        $this->assertTrue($documentController->isViewParameter('url'));
        $this->assertSame(
            "id_e=1&search=&type=actes-generique&lastetat=modification",
            $documentController->getViewParameter()['url']
        );
    }

	/**
	 * @throws ForbiddenException
	 * @throws NotFoundException
	 * @throws UnrecoverableException
	 */
    public function testEditOnlyProperties(){
		$document_info = $this->createDocument('test');
		/** @var DocumentControler $documentController */
		$documentController = $this->getControlerInstance(DocumentControler::class);
		$this->setGetInfo([
			'id_d'=>$document_info['id_d'],
			'id_e'=>PastellTestCase::ID_E_COL
		]);

		ob_start();
		$documentController->editionAction();
		$result = ob_get_contents();
		ob_end_clean();
		$this->assertRegExp("#test_edit_only#",$result);

		ob_start();
		$documentController->detailAction();
		$result = ob_get_contents();
		ob_end_clean();
		$this->assertNotRegExp("#test_edit_only#",$result);
	}

}