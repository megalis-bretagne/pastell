<?php

class FluxAPIControllerTest extends PastellTestCase {

	/** @var  FluxAPIController */
	private $fluxAPIController;

	protected function setUp(){
		parent::setUp();
		$this->fluxAPIController = $this->getAPIController('Flux',1);
	}

	public function testListAction(){
		$list = $this->fluxAPIController->get();
		$this->assertEquals('Mail sécurisé',$list['mailsec']['nom']);
	}

	public function testInfoAction(){
		$this->fluxAPIController->setQueryArgs(array('test'));
		$info = $this->fluxAPIController->get();
		$this->assertEquals('test1',$info['test1']['name']);
	}

	public function testActionList(){
		$this->fluxAPIController->setRequestInfo(array('type'=>'test'));
		$info = $this->fluxAPIController->actionListAction();
		$this->assertEquals('Test',$info['test']['action-class']);
	}

	public function testInfoActionNotExists(){
		$this->fluxAPIController->setQueryArgs(array('foo'));
		$this->setExpectedException("NotFoundException","Le flux foo n'existe pas ou vous n'avez pas le droit de lecture dessus");
		$this->fluxAPIController->get();
	}

	public function testListActionNotExists(){
		$this->fluxAPIController->setRequestInfo(array('type'=>'foo'));
		$this->setExpectedException("Exception","Acces interdit type=foo,id_u=1");
		$this->fluxAPIController->actionListAction();
	}

}