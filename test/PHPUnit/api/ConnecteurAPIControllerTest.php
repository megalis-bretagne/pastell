<?php

class ConnecteurAPIControllerTest extends PastellTestCase {

	public function testListAction(){
		$list = $this->getInternalAPI()->get("/entite/0/connecteur");
		$this->assertEquals('horodateur-interne',$list[0]['id_connecteur']);
	}

	public function testCreate(){
		$info = $this->getInternalAPI()->post("/entite/1/connecteur", array('libelle'=>'Connecteur de test','id_connecteur'=>'test'));
		$this->assertEquals('Connecteur de test',$info['libelle']);
	}

	public function testCreateWithoutLibelle(){
		$this->setExpectedException("Exception","Le libellé est obligatoire.");
		$this->getInternalAPI()->post("/entite/1/connecteur", array('libelle'=>'','id_connecteur'=>'test'));
	}

	public function testCreateGlobale(){
		$info = $this->getInternalAPI()->post("/entite/0/connecteur", array('libelle'=>'Test','id_connecteur'=>'test'));
		$this->assertEquals(0,$info['id_e']);
	}

	public function testCreateNotExist(){
		$this->setExpectedException("Exception","Aucun connecteur de ce type.");
		$this->getInternalAPI()->post("/entite/1/connecteur", array('libelle'=>'Connecteur de test','id_connecteur'=>'foo'));
	}

	public function testDelete(){
		$info = $this->getInternalAPI()->delete("/entite/1/connecteur/12");
		$this->assertEquals("ok",$info['result']);
	}

	public function testDeleteNotExist(){
		$this->setExpectedException("Exception","Ce connecteur n'existe pas.");
		$this->getInternalAPI()->delete("/entite/1/connecteur/42");
	}

	public function testDeleteUsed(){
		$this->setExpectedException("Exception","Ce connecteur est utilisé par des flux :  actes-generique");
		$this->getInternalAPI()->delete("/entite/1/connecteur/1");
	}

	public function testEdit(){
		$info = $this->getInternalAPI()->patch("/entite/1/connecteur/12",array('libelle'=>'bar'));
		$this->assertEquals('bar',$info['libelle']);
	}

	public function testEditNotExist(){
		$this->setExpectedException("Exception","Ce connecteur n'existe pas.");
		$this->getInternalAPI()->patch("/entite/1/connecteur/42",array('libelle'=>'bar'));
	}

	public function testEditNotLibelle(){
		$this->setExpectedException("Exception","Le libellé est obligatoire.");
		$this->getInternalAPI()->patch("/entite/1/connecteur/12",array('libelle'=>''));
	}

	//TODO
	/*
	public function testAssociateFluxAction(){
		$this->connecteurController->setRequestInfo(array('id_e'=>1,'id_ce'=>12,'flux'=>'test','type'=>'test'));
		$this->connecteurController->associateFluxAction();
		$this->connecteurController->setRequestInfo(array('id_e'=>1,'flux'=>'test','type'=>'test'));
		$info = $this->connecteurController->rechercheAction();
		$this->assertEquals('test',$info[0]['type']);
	}

	public function testDeleteFluxConnecteurAction(){
		$this->connecteurController->setRequestInfo(array('id_e'=>1,'id_fe'=>1));
		$this->connecteurController->deleteFluxConnecteurAction();
		$this->connecteurController->setRequestInfo(array('id_e'=>1,'flux'=>'actes-generique','type'=>'signature'));
		$info = $this->connecteurController->rechercheAction();
		$this->assertEmpty($info);
	}

	public function testDeleteFluxConnecteurNotExist(){
		$this->connecteurController->setRequestInfo(array('id_e'=>1,'id_fe'=>42));
		$this->setExpectedException("Exception","Le connecteur-flux n'existe pas : {id_fe=42}");
		$this->connecteurController->deleteFluxConnecteurAction();
	}

	public function testDeleteFluxConnecteurNotExistForEntity(){
		$this->connecteurController->setRequestInfo(array('id_e'=>2,'id_fe'=>1));
		$this->setExpectedException("Exception","Le connecteur-flux n'existe pas sur l'entité spécifié : {id_fe=1, id_e=2}");
		$this->connecteurController->deleteFluxConnecteurAction();
	}

	public function testEditContentAction(){
		$this->connecteurController->setRequestInfo(array('id_ce'=>12,'id_e'=>1,'champs1'=>'foo'));
		$this->connecteurController->editContentAction();
		$info = $this->connecteurController->detailAction();
		$this->assertEquals('foo',$info['data']['champs1']);
	}

	public function testEditContentOnChangeAction(){
		$this->connecteurController->setRequestInfo(array('id_ce'=>12,'id_e'=>1,'champs3'=>'foo'));
		$this->connecteurController->editContentAction();
		$info = $this->connecteurController->detailAction();
		$this->assertEquals('foo',$info['data']['champs4']);
	}

	private function associateConnecteurTest(){
		$this->connecteurController->setRequestInfo(array('id_e'=>1,'id_ce'=>12,'flux'=>'test','type'=>'test'));
		$this->connecteurController->associateFluxAction();
	}

	public function testDoActionAction(){
		$this->associateConnecteurTest();
		$this->connecteurController->setRequestInfo(array('id_ce'=>12,'id_e'=>1,'flux'=>'test','type'=>'test','action'=>'ok'));
		$result = $this->connecteurController->doActionAction();
		$this->assertEquals("OK !",$result['message']);
	}

	public function testDoActionNotExist(){
		$this->associateConnecteurTest();
		$this->connecteurController->setRequestInfo(array('id_ce'=>12,'id_e'=>1,'flux'=>'test','type'=>'test','action'=>'foo'));
		$this->setExpectedException("Exception","L'action foo n'existe pas.");
		$this->connecteurController->doActionAction();
	}

	public function testDoActionFail(){
		$this->associateConnecteurTest();
		$this->connecteurController->setRequestInfo(array('id_ce'=>12,'id_e'=>1,'flux'=>'test','type'=>'test','action'=>'fail'));
		$this->setExpectedException("Exception","Fail !");
		$this->connecteurController->doActionAction();
	}

	public function testDoActionNotPossible(){
		$this->associateConnecteurTest();
		$this->connecteurController->setRequestInfo(array('id_ce'=>12,'id_e'=>1,'flux'=>'test','type'=>'test','action'=>'not_possible'));
		$this->setExpectedException("Exception","L'action « not_possible »  n'est pas permise : role_id_e n'est pas vérifiée");
		$this->connecteurController->doActionAction();
	}

	public function testDoActionNoConnecteur(){
		$this->connecteurController->setRequestInfo(array('id_ce'=>12,'id_e'=>1,'flux'=>'test','type'=>'test','action'=>'ok'));
		$this->setExpectedException("Exception","Le connecteur de type test n'existe pas pour le flux test.");
		$this->connecteurController->doActionAction();
	}



	public function testInfoAction(){
		$this->setExpectedException("Exception");
		$this->connecteurController->infoAction();
		
	}
	*/
	//

}