<?php

class UtilisateurRoleAPIControllerTest extends PastellTestCase {

	/** @var  UtilisateurRoleAPIController */
	private $utilisateurRoleAPIController;

	protected function setUp() {
		parent::setUp();
		$this->utilisateurRoleAPIController = $this->getAPIController('UtilisateurRole',1);
	}

	public function testList(){
		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>1));
		$list = $this->utilisateurRoleAPIController->listAction();
		$this->assertEquals('admin',$list[0]['role']);
	}

	public function testAdd(){
		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>1,'id_e'=>0,'role'=>'utilisateur'));
		$this->utilisateurRoleAPIController->addAction();
		
		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>1));
		$list = $this->utilisateurRoleAPIController->listAction();
		$this->assertEquals('utilisateur',$list[1]['role']);
	}

	public function testAddBadUtilisateur(){
		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>42,'id_e'=>0,'role'=>'utilisateur'));
		$this->setExpectedException("Exception","L'utilisateur n'existe pas : {id_u=42}");
		$this->utilisateurRoleAPIController->addAction();
	}

	public function testAddBadRole(){
		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>1,'id_e'=>0,'role'=>'foo'));
		$this->setExpectedException("Exception","Le role spécifié n'existe pas {role=foo}");
		$this->utilisateurRoleAPIController->addAction();
	}

	public function testAddSeveral(){
		$this->utilisateurRoleAPIController->setRequestInfo(
			array(
				'id_u'=>2,
				'id_e'=>1,
				'deleteRoles' => true,
				'role'=>array('utilisateur','autre')
			)
		);
		$this->utilisateurRoleAPIController->addSeveralAction();

		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>2));
		$list = $this->utilisateurRoleAPIController->listAction();
		$this->assertEquals('autre',$list[0]['role']);
	}

	public function testAddSeveralNoRole(){
		$this->utilisateurRoleAPIController->setRequestInfo(
			array(
				'id_u'=>2,
				'id_e'=>1,

			)
		);
		$this->assertFalse($this->utilisateurRoleAPIController->addSeveralAction());
	}

	public function testAddSeveralOneRole(){
		$this->utilisateurRoleAPIController->setRequestInfo(
			array(
				'id_u'=>2,
				'id_e'=>1,
				'role'=>'autre',
				'deleteRoles'=>true

			)
		);
		$this->utilisateurRoleAPIController->addSeveralAction();
		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>2,'id_e'=>1));
		$list = $this->utilisateurRoleAPIController->listAction();
		$this->assertEquals('autre',$list[0]['role']);
	}


	public function testDelete(){
		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>2,'id_e'=>1,'role'=>'admin'));
		$this->utilisateurRoleAPIController->deleteAction();

		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>2));
		$list = $this->utilisateurRoleAPIController->listAction();
		$this->assertEquals('aucun droit',$list[0]['role']);
	}

	public function testDeleteSeveral(){
		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>2,'id_e'=>1,'role'=>array('admin')));
		$this->utilisateurRoleAPIController->deleteSeveralAction();

		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>2));
		$list = $this->utilisateurRoleAPIController->listAction();
		$this->assertEquals('aucun droit',$list[0]['role']);
	}

	public function testDeleteSevaralNoRole(){
		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>2,'id_e'=>1));
		$this->assertFalse($this->utilisateurRoleAPIController->deleteSeveralAction());
	}

	public function testDeleteSeveralOneRole(){
		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>2,'id_e'=>1,'role'=>'admin'));
		$this->utilisateurRoleAPIController->deleteSeveralAction();

		$this->utilisateurRoleAPIController->setRequestInfo(array('id_u'=>2));
		$list = $this->utilisateurRoleAPIController->listAction();
		$this->assertEquals('aucun droit',$list[0]['role']);
	}

}