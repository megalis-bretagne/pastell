<?php

class ActionPossibleTest extends PastellTestCase {

	/** @var  ActionPossible */
	private $actionPossible;

	private $id_d;

	protected function setUp() {
		parent::setUp();
		$this->actionPossible = new ActionPossible($this->getObjectInstancier());
		/** @var DocumentAPIController $documentAPIController */
		$documentAPIController = $this->getAPIController('document',1);
		$documentAPIController->setRequestInfo(array('id_e'=>1,'type'=>'test'));
		$info = $documentAPIController->createAction();
		$this->id_d = $info['id_d'];
	}

	public function testIsCreationPossible(){
		$this->assertTrue($this->actionPossible->isCreationPossible(1,1,'test'));
	}

	public function testGetActionPossible(){
		$result = $this->actionPossible->getActionPossible(1,1,$this->id_d);
		$this->assertEquals('modification',$result[0]);
	}




}