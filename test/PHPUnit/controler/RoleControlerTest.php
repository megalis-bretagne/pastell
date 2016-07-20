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
	
	/**
	 * @expectedException LastMessageException
	 */
	public function testDoEditionAction(){
		$_POST = array('role'=>'test','libelle'=>'test');
		$this->roleControler->doEditionAction();
	}
	
	/**
	 * @expectedException LastMessageException
	 */
	public function testDoDeleteAction(){
		$this->roleControler->doDeleteAction();
	}
	
	/**
	 * @expectedException LastMessageException
	 */
	public function testDoDetailAction(){
		$_POST = array('role'=>'test','droit'=>array('system:lecture'=>'selected'));
		$this->roleControler->doDetailAction();
	}
}