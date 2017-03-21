<?php

class ExtensionAPIControllerTest extends PastellTestCase {

	public function testList(){
		$list = $this->getInternalAPI()->get("/extension");
		$this->assertEquals('/var/lib/pastell/pastell_cdg59',$list['result'][1]['path']);
	}

	public function testEdit(){
		$list = $this->getInternalAPI()->put("/extension/1",array('path'=>'/tmp'));
		$this->assertEquals('/tmp',$list['detail']['path']);
	}

	public function testEditPathNotFound(){
		$this->setExpectedException("Exception","Le chemin « /foo/bar » n'existe pas sur le système de fichier");
		$this->getInternalAPI()->put("/extension/1",array('path'=>'/foo/bar'));
	}

	public function testEditExtensionNotFound(){
		$this->setExpectedException("Exception","Extension #42 non trouvée");
		$this->getInternalAPI()->put("/extension/42",array('path'=>'/tmp'));
	}

	public function testEditAlreadyExists(){
		$this->getInternalAPI()->post("/extension",array('path'=>__DIR__.'/../fixtures/extensions/extension-test'));
		$this->setExpectedException("ConflictException","L'extension #glaneur est déja présente");
		$this->getInternalAPI()->post("/extension",array('path'=>__DIR__.'/../fixtures/extensions/extension-test'));
	}

	public function testDeleteAction(){
		$this->getInternalAPI()->delete("/extension/1");
		$list = $this->getInternalAPI()->get("/extension");
		$this->assertTrue(empty($list['result'][1]));
	}

	public function testDeleteActionNotFound(){
		$this->setExpectedException("Exception","Extension #42 non trouvée");
		$this->getInternalAPI()->delete("/extension/42");
	}

	public function testV1list(){
		$this->expectOutputRegex("#manifest#");
		$this->getV1("list-extension.php");
	}

	public function testV1create(){
		$this->expectOutputRegex("#manifest.yml absent#");
		$this->getV1("edit-extension.php",array("path"=>'/tmp'));
	}

	public function testV1edit(){
		$this->expectOutputRegex("#/tmp#");
		$this->getV1("edit-extension.php",array("path"=>'/tmp','id_extension'=>1));
	}

	public function testV1delete(){
		$this->expectOutputRegex("#ok#");
		$this->getV1("delete-extension.php",array('id_extension'=>1));
	}

}