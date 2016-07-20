<?php

class RoleControlerTest extends ControlerTestCase {

	/** @var  RoleControler */
	private $roleControler;
	
	public function setUp(){
		parent::setUp();
		$this->roleControler = $this->getControlerInstance("RoleControler");
	}

	public function testIndexAction(){
		$this->expectOutputRegex("##");
		$this->roleControler->indexAction();
	}
	
	public function testDetailAction(){
		$this->expectOutputRegex("##");
		$this->roleControler->detailAction();
	}
	
	public function testEditionAction(){
		$this->expectOutputRegex("##");
		$this->roleControler->editionAction();
	}
	
	public function testEditionAction2(){
		$this->expectOutputRegex("##");
		$_GET = array('role'=>'admin');
		$this->roleControler->editionAction();
	}

	public function testDoEditionAction(){
		$this->setExpectedException("LastMessageException");
		$this->setPostInfo(array('role'=>'test','libelle'=>'test'));
		$this->roleControler->doEditionAction();
	}

	public function testDoDeleteAction(){
		$this->setExpectedException("LastMessageException");
		$this->roleControler->doDeleteAction();
	}

	public function testDoDetailAction(){
		$this->setExpectedException("LastMessageException");
		$this->setPostInfo(array('role'=>'test','droit'=>array('system:lecture'=>'selected')));
		$this->roleControler->doDetailAction();
	}
}