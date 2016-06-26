<?php

class RoleAPIControllerTest extends PastellTestCase {

	/** @var  RoleAPIController */
	private $roleAPIController;

	protected function setUp() {
		parent::setUp();
		$this->roleAPIController = $this->getAPIController('Role',1);
	}

	public function testList(){
		$list = $this->roleAPIController->listAction();
		$this->assertEquals('admin',$list[0]['role']);
	}


	public function testListFailed(){
		$this->roleAPIController = $this->getAPIController('Role',42);
		$this->setExpectedException("Exception","Acces interdit type=role:lecture,id_u=42");
		$this->roleAPIController->listAction();
	}

}