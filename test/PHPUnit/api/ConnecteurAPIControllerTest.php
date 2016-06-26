<?php

class ConnecteurAPIControllerTest extends PastellTestCase {

	/** @var  ConnecteurAPIController */
	private $connecteurController;

	protected function setUp() {
		parent::setUp();
		$this->connecteurController = $this->getAPIController('Connecteur',1);
	}

	public function testListAction(){
		$list = $this->connecteurController->listAction();
		$this->assertEquals('horodateur-interne',$list[0]['id_connecteur']);
	}

	public function testCreate(){
		$this->connecteurController->setRequestInfo(array('libelle'=>'Connecteur de test','id_connecteur'=>'test','id_e'=>1));
		$info = $this->connecteurController->createAction();
		$this->assertNotEmpty($info['id_ce']);
		$this->connecteurController->setRequestInfo(array('id_ce'=>$info['id_ce']));
		$info = $this->connecteurController->detailAction();
		$this->assertEquals('Connecteur de test',$info['libelle']);
	}

	public function testCreateWithoutLibelle(){
		$this->connecteurController->setRequestInfo(array('libelle'=>'','id_connecteur'=>'test','id_e'=>1));
		$this->setExpectedException("Exception","Le libellé est obligatoire.");
		$this->connecteurController->createAction();

	}

	public function testCreateGlobale(){
		$this->connecteurController->setRequestInfo(array('libelle'=>'Test global','id_connecteur'=>'test','id_e'=>0));
		$info = $this->connecteurController->createAction();
		$this->connecteurController->setRequestInfo(array('id_ce'=>$info['id_ce']));
		$info = $this->connecteurController->detailAction();
		$this->assertEquals(0,$info['id_e']);
	}

	public function testCreateNotExist(){
		$this->connecteurController->setRequestInfo(array('libelle'=>'test','id_connecteur'=>'foo','id_e'=>1));
		$this->setExpectedException("Exception","Aucun connecteur de ce type.");
		$this->connecteurController->createAction();
	}

	public function testDelete(){
		$this->connecteurController->setRequestInfo(array('id_ce'=>12));
		$this->connecteurController->deleteAction();
		$this->setExpectedException("Exception","Ce connecteur n'existe pas.");
		$this->connecteurController->detailAction();
	}

	public function testDeleteNotExist(){
		$this->connecteurController->setRequestInfo(array('id_ce'=>42));
		$this->setExpectedException("Exception","Ce connecteur n'existe pas.");
		$this->connecteurController->deleteAction();
	}

	public function testDeleteUsed(){
		$this->connecteurController->setRequestInfo(array('id_ce'=>1));
		$this->setExpectedException("Exception","Ce connecteur est utilisé par des flux :  actes-generique");
		$this->connecteurController->deleteAction();
	}

	public function testEdit(){
		$this->connecteurController->setRequestInfo(array('id_ce'=>12,'libelle'=>'bar'));
		$this->connecteurController->editAction();
		$info = $this->connecteurController->detailAction();
		$this->assertEquals('bar',$info['libelle']);
	}

	public function testEditNotExist(){
		$this->connecteurController->setRequestInfo(array('id_ce'=>42,'libelle'=>'bar'));
		$this->setExpectedException("Exception","Ce connecteur n'existe pas.");
		$this->connecteurController->editAction();
	}

	public function testEditNotLibelle(){
		$this->connecteurController->setRequestInfo(array('id_ce'=>12,'libelle'=>''));
		$this->setExpectedException("Exception","Le libellé est obligatoire.");
		$this->connecteurController->editAction();
	}

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


}