<?php

class EntiteAPIControllerTest extends PastellTestCase {

	/** @var  EntiteAPIController */
	private $entiteController;

	protected function setUp() {
		parent::setUp();
		$this->entiteController = $this->getAPIController('Entite',1);
	}

	public function testList(){
		$list = $this->entiteController->listAction();
		$this->assertEquals('Bourg-en-Bresse',$list[0]['denomination']);
	}

	public function testCreate(){
		$this->entiteController->setRequestInfo(
			array(
					'denomination'=>'Métropolis',
					'type'=>'collectivite',
					'siren'=>'677203002'
			)
		);
		$result = $this->entiteController->createAction();
		$this->assertNotEmpty($result['id_e']);
	}

	public function testDelete(){
		$this->entiteController->setRequestInfo(array('id_e'=>2));
		$result = $this->entiteController->deleteAction();
		$this->assertEquals('ok',$result['result']);

		$this->setExpectedException("Exception","Acces interdit id_e=2, droit=entite:lecture,id_u=1");
		$this->entiteController->detailAction();
	}

	public function testEdit(){
		$this->entiteController->setRequestInfo(array('id_e'=>1,'denomination'=>'Mâcon','siren'=>'677203002'));
		$this->entiteController->editAction();
		$info = $this->entiteController->detailAction();
		$this->assertEquals('Mâcon',$info['denomination']);
	}

	public function testDetail(){
		$this->entiteController->setRequestInfo(array('id_e'=>1));
		$info = $this->entiteController->detailAction();
		$this->assertEquals('Bourg-en-Bresse',$info['denomination']);
	}

	public function testCreateWithEditAction(){
		$this->entiteController->setRequestInfo(
			array(
				'denomination'=>'Métropolis',
				'type'=>'collectivite',
				'siren'=>'677203002',
				'create'=>true
			)
		);
		$result = $this->entiteController->editAction();
		$this->assertNotEmpty($result['id_e']);
	}

	public function testCreateFille(){
		$this->entiteController->setRequestInfo(
			array(
				'id_e'=>2,
				'denomination'=>'Métropolis',
				'type'=>'collectivite',
				'siren'=>'677203002',
				'centre_de_gestion'=> 1,
			)
		);
		$this->entiteController->editAction();
		//On le fait une seconde fois pour attraper la modif sur le cdg..
		$this->entiteController->editAction();
		$this->entiteController->setRequestInfo(array('id_e'=>2));
		$info = $this->entiteController->detailAction();
		$this->assertEquals(1,$info['centre_de_gestion']);
	}

	public function testCreateWithoutName(){
		$this->entiteController->setRequestInfo(array());
		$this->setExpectedException("Exception","Le nom est obligatoire");
		$this->entiteController->createAction();
	}

	public function testCreateWithoutType(){
		$this->entiteController->setRequestInfo(array("denomination"=>"toto"));
		$this->setExpectedException("Exception","Le type d'entité doit être renseigné");
		$this->entiteController->createAction();
	}

	public function testCreateWithoutSiren(){
		$this->entiteController->setRequestInfo(array("denomination"=>"toto",'type'=>Entite::TYPE_COLLECTIVITE));
		$this->setExpectedException("Exception","Le siren est obligatoire");
		$this->entiteController->createAction();
	}

	public function testCreateBadSiren(){
		$this->entiteController->setRequestInfo(
			array(
				"denomination"=>"toto",
				'type'=>Entite::TYPE_COLLECTIVITE,
				'siren' => '123456789'
			)
		);
		$this->setExpectedException("Exception","Le siren « 123456789 » ne semble pas valide");
		$this->entiteController->createAction();
	}

	public function testCreateServiceInRootEntite(){
		$this->entiteController->setRequestInfo(
			array(
				"denomination"=>"toto",
				'type'=>Entite::TYPE_SERVICE,
				'siren' => '123456789'
			)
		);
		$this->setExpectedException("Exception","Un service doit être ataché à une entité mère");
		$this->entiteController->createAction();
	}



}