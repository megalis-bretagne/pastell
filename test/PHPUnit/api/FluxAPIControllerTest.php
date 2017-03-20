<?php

class FluxAPIControllerTest extends PastellTestCase {

	public function testListAction(){
		$list = $this->getInternalAPI()->get("/flux");
		$this->assertEquals('Mail sécurisé',$list['mailsec']['nom']);
	}

	public function testInfoAction(){
		$info = $this->getInternalAPI()->get("/flux/test");
		$this->assertEquals('test1',$info['test1']['name']);
	}

	public function testActionList(){
		$info = $this->getInternalAPI()->get("/flux/test/action");
		$this->assertEquals('Test',$info['test']['action-class']);
	}

	public function testInfoActionNotExists(){
		$this->setExpectedException("NotFoundException","Le flux foo n'existe pas sur cette plateforme");
		$this->getInternalAPI()->get("/flux/foo");
	}

	public function testListActionNotExists(){
		$this->setExpectedException("Exception","Le flux foo n'existe pas sur cette plateforme");
		$this->getInternalAPI()->get("/flux/foo/action");
	}

}