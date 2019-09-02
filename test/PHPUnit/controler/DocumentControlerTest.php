<?php

class DocumentControlerTest extends PastellTestCase {

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

	public function testIndexWithoutRight(){
		$utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class);
		$id_u = $utilisateurSQL->create("badguy","foo","foo","test@bar.baz");

		$roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
		$roleUtilisateur->addRole($id_u,"admin",2);
		$this->getObjectInstancier()->Authentification->Connexion('admin',$id_u);

		$documentController = $this->getObjectInstancier()->getInstance(DocumentControler::class);
		$documentController->setGetInfo(new Recuperateur(['id_e' => 1,]));
		try {
			ob_start(); //Very uggly...
			$documentController->indexAction();
			$this->assertTrue(false);
		} catch (Exception $e){
			/* Nothing to do */
		}
		ob_end_clean();
		$this->assertEquals(
			"Vous n'avez pas les droits nécessaires (1:actes-automatique:lecture) pour accéder à cette page",
			$documentController->getLastError()->getLastError()
		);
	}
}