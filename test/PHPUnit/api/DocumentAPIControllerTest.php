<?php


class DocumentAPIControllerTest extends PastellTestCase {

	/** @var  DocumentAPIController */
	private $documentAPIController;

	protected function setUp() {
		parent::setUp();
		$this->documentAPIController = $this->getAPIController('Document',1);
	}

	private function createDocument(){
		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'type'=>'test'));
		$info = $this->documentAPIController->createAction();
		return $info['id_d'];
	}

	public function testList(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(array('type'=>'test','id_e'=>1));
		$list = $this->documentAPIController->listAction();
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testDetail(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'id_d'=>$id_d));
		$info = $this->documentAPIController->detailAction();
		$this->assertEquals('test',$info['info']['type']);
	}

	public function testDetailAll(){
		$id_d_1 = $this->createDocument();
		$id_d_2 = $this->createDocument();
		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'id_d'=>array($id_d_1,$id_d_2)));
		$list = $this->documentAPIController->detailAllAction();
		$this->assertEquals($id_d_1,$list[$id_d_1]['info']['id_d']);
		$this->assertEquals($id_d_2,$list[$id_d_2]['info']['id_d']);
	}

	public function testDetailAllFail(){
		$this->documentAPIController->setRequestInfo(array('id_e'=>1));
		$this->setExpectedException("Exception","Le paramètre id_d[] ne semble pas valide");
		$this->documentAPIController->detailAllAction();
	}

	public function testRecherche(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'date_in_fr'=>true));
		$list = $this->documentAPIController->rechercheAction();
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testRechercheNoIdEntite(){
		$this->createDocument();
		$this->documentAPIController->setRequestInfo(array());
		$this->setExpectedException("Exception","id_e est obligatoire");
		$this->documentAPIController->rechercheAction();
	}

	public function testRechercheIndexedField(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'id_d'=>$id_d,'test1'=>'toto'));
		$this->documentAPIController->editAction();

		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'type'=>'test','test1'=>'toto'));
		$list = $this->documentAPIController->rechercheAction();
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testRechercheIndexedDateField(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'id_d'=>$id_d,'date_indexed'=>'2001-09-11'));
		$this->documentAPIController->editAction();

		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'type'=>'test',
				'date_indexed'=>'2001-09-11',
				'date_in_fr' => true
				)

		);
		$list = $this->documentAPIController->rechercheAction();
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testExternalData(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'id_d'=>$id_d,'field'=>'test_external_data'));
		$list = $this->documentAPIController->externalDataAction();
		$this->assertEquals("Spock",$list[4]);
	}

	public function testExternalDataFaild(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'id_d'=>$id_d,'field'=>'test42'));
		$this->setExpectedException("Exception","Type test42 introuvable");
		$this->documentAPIController->externalDataAction();
	}
	

	public function testEditAction(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'id_d'=>$id_d,'test1'=>'toto'));
		$this->documentAPIController->editAction();
		$this->documentAPIController->setRequestInfo(array('id_e'=>1,'id_d'=>$id_d));
		$info = $this->documentAPIController->detailAction();
		$this->assertEquals("toto",$info['data']['test1']);
	}

	private function sendFile($id_d){
		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
				'field_name'=>'fichier',
				'file_name'=>'toto.txt',
				'file_content'=>'xxxx'
			)
		);
		$this->documentAPIController->sendFileAction();
	}

	public function testSendFile(){
		$id_d = $this->createDocument();
		$this->sendFile($id_d);
		$info = $this->documentAPIController->detailAction();
		$this->assertEquals("toto.txt",$info['data']['fichier'][0]);
	}

	public function testReceiveFile(){
		$id_d = $this->createDocument();
		$this->sendFile($id_d);
		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
				'field_name'=>'fichier',
			)
		);
		$info = $this->documentAPIController->receiveFileAction();
		$this->assertEquals("xxxx",$info['file_content']);
	}

	public function testAction(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
				'action'=>'ok',
			)
		);
		$info = $this->documentAPIController->actionAction();
		$this->assertEquals("OK !",$info['message']);
	}

	public function testActionNotPossible(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
				'action'=>'not-possible',
			)
		);
		$this->setExpectedException(
			"Exception",
			"L'action « not-possible »  n'est pas permise : role_id_e n'est pas vérifiée"
		);
		$this->documentAPIController->actionAction();
	}

	public function testActionFailed(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
				'action'=>'fail',
			)
		);
		$this->setExpectedException(
			"Exception",
			"Raté !"
		);
		$this->documentAPIController->actionAction();

	}

	public function testEditOnChange(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
				'test_on_change'=>'foo',
			)
		);
		$this->documentAPIController->editAction();
		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
			)
		);
		$info = $this->documentAPIController->detailAction();
		$this->assertEquals("foo",$info['data']['test2']);

	}

	public function testEditCantModify(){
		$id_d = $this->createDocument();
		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
				'test2'=>'ok',
			)
		);
		$this->documentAPIController->editAction();
		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
				'action'=>'no-way',
			)
		);
		$this->documentAPIController->actionAction();

		$this->setExpectedException("Exception","L'action « modification »  n'est pas permise");
		$this->documentAPIController->editAction();
	}

	public function testRecuperationFichier(){
		$id_d = $this->createDocument();
		$this->sendFile($id_d);

		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
				'field'=>'fichier',

			)
		);

		$this->setExpectedException("Exception","Exit called with code 0");
		$this->expectOutputRegex("#xxxx#");
		$this->documentAPIController->recuperationFichierAction();
	}

	public function testRecuperationFichierFailed(){
		$id_d = $this->createDocument();

		$this->documentAPIController->setRequestInfo(
			array(
				'id_e'=>1,
				'id_d'=>$id_d,
				'field'=>'fichier',

			)
		);

		$this->setExpectedException("Exception","Ce fichier n'existe pas");
		$this->documentAPIController->recuperationFichierAction();
	}


}