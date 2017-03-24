<?php

class ActionPossibleTest extends PastellTestCase {

	/** @var  ActionPossible */
	private $actionPossible;

	private $id_d;

	protected function setUp() {
		parent::setUp();
		$this->actionPossible = $this->getObjectInstancier()->getInstance("ActionPossible");
		$info = $this->getInternalAPI()->post("entite/1/document/",array("type"=>"test"));
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