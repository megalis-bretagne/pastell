<?php


class DocumentAPIControllerTest extends PastellTestCase {

	private function createDocument(){
		$info = $this->getInternalAPI()->post("entite/1/document",array('type'=>'test'));
		return $info['id_d'];
	}

	public function testList(){
		$id_d = $this->createDocument();
		$list = $this->getInternalAPI()->get("entite/1/document");
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testDetail(){
		$id_d = $this->createDocument();
		$info = $this->getInternalAPI()->get("entite/1/document/$id_d");
		$this->assertEquals('test',$info['info']['type']);
	}

	public function testDetailAll(){
		$id_d_1 = $this->createDocument();
		$id_d_2 = $this->createDocument();
		$list = $this->getInternalAPI()->get("entite/1/document/?id_d[]=$id_d_1&id_d[]=$id_d_2");
		$this->assertEquals($id_d_1,$list[$id_d_1]['info']['id_d']);
		$this->assertEquals($id_d_2,$list[$id_d_2]['info']['id_d']);
	}

	public function testDetailAllFail(){
		$this->setExpectedException("Exception","Le paramètre id_d[] ne semble pas valide");
		$this->getInternalAPI()->get("entite/1/document/?id_d=42");
	}

	public function testRecherche(){
		$id_d = $this->createDocument();
		$list = $this->getInternalAPI()->get("entite/1/document?date_in_fr=true");
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testRechercheNoIdEntite(){
		$this->setExpectedException("Exception","id_e est obligatoire");
		$this->getInternalAPI()->get("entite/0/document");
	}

	public function testRechercheIndexedField(){
		$id_d = $this->createDocument();
		$this->getInternalAPI()->patch("entite/1/document/$id_d",array('test1'=>'toto'));
		$list = $this->getInternalAPI()->get("entite/1/document?test1=toto");
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testRechercheIndexedDateField(){
		$id_d = $this->createDocument();
		$this->getInternalAPI()->patch("entite/1/document/$id_d",array('date_indexed'=>'2001-09-11'));
		$list = $this->getInternalAPI()->get("entite/1/document?type=test&date_in_fr=true&date_indexed=2001-09-11");
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testExternalData(){
		$id_d = $this->createDocument();
		$list = $this->getInternalAPI()->get("entite/1/document/$id_d/externalData/test_external_data");
		$this->assertEquals("Spock",$list[4]);
	}

	public function testExternalDataFaild(){
		$id_d = $this->createDocument();
		$this->setExpectedException("Exception","Type test42 introuvable");
		$this->getInternalAPI()->get("entite/1/document/$id_d/externalData/test42");
	}
	

	public function testEditAction(){
		$id_d = $this->createDocument();
		$info = $this->getInternalAPI()->patch("entite/1/document/$id_d",array('test1'=>'toto'));
		$this->assertEquals("toto",$info['content']['data']['test1']);
	}

	private function sendFile($id_d){
		$info = $this->getInternalAPI()->post("entite/1/document/$id_d/file/fichier",
			array(
				'file_name'=>'toto.txt',
				'file_content'=>'xxxx'
			)
		);
		return $info;
	}

	public function testSendFile(){
		$id_d = $this->createDocument();
		$info = $this->sendFile($id_d);
		$this->assertEquals("toto.txt",$info['content']['data']['fichier'][0]);
	}

	public function testReceiveFile(){
		$id_d = $this->createDocument();
		$this->sendFile($id_d);
		$info =$this->getInternalAPI()->get("entite/1/document/$id_d/file/fichier?receive=true");
		$this->assertEquals("xxxx",$info['file_content']);
	}

	public function testAction(){
		$id_d = $this->createDocument();
		$info =$this->getInternalAPI()->post("entite/1/document/$id_d/action/ok");
		$this->assertEquals("OK !",$info['message']);
	}

	public function testActionNotPossible(){
		$id_d = $this->createDocument();
		$this->setExpectedException(
			"Exception",
			"L'action « not-possible »  n'est pas permise : role_id_e n'est pas vérifiée"
		);
		$this->getInternalAPI()->post("entite/1/document/$id_d/action/not-possible");
	}

	public function testActionFailed(){
		$id_d = $this->createDocument();
		$this->setExpectedException(
			"Exception",
			"Raté !"
		);
		$this->getInternalAPI()->post("entite/1/document/$id_d/action/fail");
	}

	public function testEditOnChange(){
		$id_d = $this->createDocument();
		$info =$this->getInternalAPI()->patch("entite/1/document/$id_d",array('test_on_change'=>'foo'));
		$this->assertEquals("foo",$info['content']['data']['test2']);
	}

	public function testEditCantModify(){
		$id_d = $this->createDocument();
		$this->getInternalAPI()->post("entite/1/document/$id_d/action/no-way");
		$this->setExpectedException("Exception","L'action « modification »  n'est pas permise");
		$this->getInternalAPI()->patch("entite/1/document/$id_d",array('test2'=>'ok'));
	}

	public function testRecuperationFichier(){
		$id_d = $this->createDocument();
		$this->sendFile($id_d);
		$this->setExpectedException("Exception","Exit called with code 0");
		$this->expectOutputRegex("#xxxx#");
		$this->getInternalAPI()->get("entite/1/document/$id_d/file/fichier");
	}

	public function testRecuperationFichierFailed(){
		$id_d = $this->createDocument();
		$this->setExpectedException("Exception","Ce fichier n'existe pas");
		$this->getInternalAPI()->get("entite/1/document/$id_d/file/fichier");
	}
}