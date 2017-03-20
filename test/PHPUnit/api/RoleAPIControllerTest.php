<?php

class RoleAPIControllerTest extends PastellTestCase {

	/** @var  RoleAPIController */
	private $roleAPIController;

	protected function setUp() {
		parent::setUp();
		$this->roleAPIController = $this->getAPIController('Role',1);
	}

	public function testList(){
		$list = $this->roleAPIController->get();
		$this->assertEquals('admin',$list[0]['role']);
	}

	public function testListFailed(){
		$this->roleAPIController = $this->getAPIController('Role',42);
		$this->setExpectedException("Exception","Vous devez avoir le droit role:lecture pour accéder à la ressource.");
		$this->roleAPIController->get();
	}

}