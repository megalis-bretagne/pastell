<?php

class DocumentTypeAPIControllerTest extends PastellTestCase {

	/** @var  DocumentTypeAPIController */
	private $documentTypeAPIController;

	protected function setUp(){
		parent::setUp();
		$this->documentTypeAPIController = $this->getAPIController('DocumentType',1);
	}

	public function testListAction(){
		$list = $this->documentTypeAPIController->listAction();
		$this->assertEquals('Mail sécurisé',$list['mailsec']['nom']);
	}

	public function testInfoAction(){
		$this->documentTypeAPIController->setRequestInfo(array('type'=>'test'));
		$info = $this->documentTypeAPIController->infoAction();
		$this->assertEquals('test1',$info['test1']['name']);
	}

	public function testActionList(){
		$this->documentTypeAPIController->setRequestInfo(array('type'=>'test'));
		$info = $this->documentTypeAPIController->actionListAction();
		$this->assertEquals('Test',$info['test']['action-class']);
	}

	public function testInfoActionNotExists(){
		$this->documentTypeAPIController->setRequestInfo(array('type'=>'foo'));
		$this->setExpectedException("Exception","Acces interdit type=foo,id_u=1");
		$this->documentTypeAPIController->infoAction();
	}

	public function testListActionNotExists(){
		$this->documentTypeAPIController->setRequestInfo(array('type'=>'foo'));
		$this->setExpectedException("Exception","Acces interdit type=foo,id_u=1");
		$this->documentTypeAPIController->actionListAction();
	}

}