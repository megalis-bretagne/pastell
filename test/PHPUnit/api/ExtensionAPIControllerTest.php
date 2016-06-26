<?php


class ExtensionAPIControllerTest extends PastellTestCase {

	/** @var  ExtensionAPIController */
	private $extensionController;

	protected function setUp(){
		parent::setUp();
		$this->extensionController = $this->getAPIController('Extension',1);
	}

	public function testList(){
		$list = $this->extensionController->listAction();
		$this->assertEquals('/var/lib/pastell/pastell_cdg59',$list['result'][1]['path']);
	}

	public function testEdit(){
		$this->extensionController->setRequestInfo(array('id_extension'=>1,'path'=>'/tmp'));
		$this->extensionController->editAction();
		$list = $this->extensionController->listAction();
		$this->assertEquals('/tmp',$list['result'][1]['path']);
	}

	public function testEditPathNotFound(){
		$this->extensionController->setRequestInfo(array('path'=>'/foo/bar'));
		$this->setExpectedException("Exception","Le chemin « /foo/bar » n'existe pas sur le système de fichier");
		$this->extensionController->editAction();
	}

	public function testEditExtensionNotFound(){
		$this->extensionController->setRequestInfo(array('id_extension'=>42,'path'=>'/tmp'));
		$this->setExpectedException("Exception","L'extension #42 est introuvable");
		$this->extensionController->editAction();
	}

	public function testEditAlreadyExists(){
		$this->extensionController->setRequestInfo(array('path'=>__DIR__.'/../fixtures/extensions/extension-test'));
		$this->extensionController->editAction();
		$this->setExpectedException("Exception","L'extension #glaneur est déja présente");
		$this->extensionController->editAction();
	}

	public function testDeleteAction(){
		$this->extensionController->setRequestInfo(array('id_extension'=>1));
		$this->extensionController->deleteAction();
		$list = $this->extensionController->listAction();
		$this->assertTrue(empty($list['result'][1]));
	}

	public function testDeleteActionNotFound(){
		$this->extensionController->setRequestInfo(array('id_extension'=>42));
		$this->setExpectedException("Exception","Extension #42 non trouvée");
		$this->extensionController->deleteAction();

	}

}