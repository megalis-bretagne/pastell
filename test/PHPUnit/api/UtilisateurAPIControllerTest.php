<?php

class UtilisateurAPIControllerTest extends PastellTestCase {

	/** @var  UtilisateurAPIController */
	private $utilisateurController;

	protected function setUp() {
		parent::setUp();
		$this->utilisateurController = $this->getAPIController('Utilisateur',1);
	}

	public function testCreate(){
		$this->utilisateurController->setRequestInfo(
			array (
				'email'=>'foo@bar.baz',
				'login'=>'foo',
				'password' => 'bar',
				'nom' => 'foo',
				'prenom' => 'bar',
			)
		);
		$result  = $this->utilisateurController->createAction();
		$this->utilisateurController->setRequestInfo(array('id_u'=>$result['id_u']));
		$info = $this->utilisateurController->detailAction();
		$this->assertEquals('foo',$info['nom']);
	}

	public function testCreateWithoutNom(){
		$this->utilisateurController->setRequestInfo(
			array (
				'email'=>'foo@bar.baz',
				'login'=>'foo',
				'password' => 'bar',
				'prenom' => 'bar',
			)
		);
		$this->setExpectedException("Exception","Le nom est obligatoire");
		$this->utilisateurController->createAction();
	}

	public function testCreateWithoutPrenom(){
		$this->utilisateurController->setRequestInfo(
			array (
				'email'=>'foo@bar.baz',
				'login'=>'foo',
				'password' => 'bar',
				'nom' => 'bar',
			)
		);
		$this->setExpectedException("Exception","Le prénom est obligatoire");
		$this->utilisateurController->createAction();
	}

	public function testCreateWithoutLogin(){
		$this->utilisateurController->setRequestInfo(
			array (
				'email'=>'foo@bar.baz',
				'prenom'=>'foo',
				'password' => 'bar',
				'nom' => 'bar',
			)
		);
		$this->setExpectedException("Exception","Il faut saisir un login");
		$this->utilisateurController->createAction();
	}

	public function testCreateWithoutEmail(){
		$this->utilisateurController->setRequestInfo(
			array (
				'login'=>'foo@bar.baz',
				'prenom'=>'foo',
				'password' => 'bar',
				'nom' => 'bar',
			)
		);
		$this->setExpectedException("Exception","Votre adresse email ne semble pas valide");
		$this->utilisateurController->createAction();
	}
	public function testCreateSameUser(){
		$this->utilisateurController->setRequestInfo(
			array (
				'login'=>'foo',
				'prenom'=>'foo',
				'password' => 'bar',
				'nom' => 'bar',
				'email'=>'foo@bar.baz',
			)
		);
		$this->utilisateurController->createAction();

		$this->utilisateurController->setRequestInfo(array('id_u'=>1,'login'=>'foo'));
		$this->setExpectedException("Exception","Un utilisateur avec le même login existe déjà.");
		$this->utilisateurController->editAction();
	}

	public function testSetCertificateKO(){
		$this->utilisateurController->setRequestInfo(
			array (
				'id_u'=>1,
			)
		);
		$fileUploader = new FileUploaderMock();
		$fileUploader->setFiles(array('certificat'=>'toto'));
		$this->utilisateurController->setFileUploader($fileUploader);
		$this->setExpectedException("Exception","Le certificat ne semble pas être valide");
		$this->utilisateurController->editAction();
	}

	public function testSetCertificate(){
		$this->utilisateurController->setRequestInfo(
			array (
				'id_u'=>1,
			)
		);
		$cert_content = file_get_contents(__DIR__."/../fixtures/autorite-cert.pem");
		$fileUploader = new FileUploaderMock();
		$fileUploader->setFiles(array('certificat'=>$cert_content));
		$this->utilisateurController->setFileUploader($fileUploader);
		$this->utilisateurController->editAction();
		$this->utilisateurController->setRequestInfo(array('id_u'=>1));
		$info = $this->utilisateurController->detailAction();
		$this->assertEquals($cert_content,$info['certificat']);
	}


	public function testList(){
		$info = $this->utilisateurController->listAction();
		$this->assertEquals('admin',$info[0]['login']);
	}

	public function testDetail(){
		$this->utilisateurController->setRequestInfo(array('id_u'=>1));
		$info = $this->utilisateurController->detailAction();
		$this->assertEquals('admin',$info['login']);
	}

	public function testDetailNotExist(){
		$this->utilisateurController->setRequestInfo(array('id_u'=>42));
		$this->setExpectedException("Exception","L'utilisateur n'existe pas : {id_u=42}");
		$this->utilisateurController->detailAction();
	}

	public function testEdit(){
		$this->utilisateurController->setRequestInfo(array('id_u'=>1,'login'=>'toto'));
		$this->utilisateurController->editAction();
		$this->utilisateurController->setRequestInfo(array('id_u'=>1));
		$info = $this->utilisateurController->detailAction();
		$this->assertEquals('toto',$info['login']);
	}

	public function testEditWithCreate(){
		$this->utilisateurController->setRequestInfo(
			array (
				'email'=>'foo@bar.baz',
				'login'=>'foo',
				'password' => 'bar',
				'nom' => 'foo',
				'prenom' => 'bar',
				'create'=>true
			)
		);
		$result = $this->utilisateurController->editAction();
		$this->utilisateurController->setRequestInfo(array('id_u'=>$result['id_u']));
		$info = $this->utilisateurController->detailAction();
		$this->assertEquals('foo',$info['login']);
	}


	public function testDelete(){
		$this->utilisateurController->setRequestInfo(array('id_u'=>1));
		$this->utilisateurController->deleteAction();
		$this->setExpectedException("Exception","L'utilisateur n'existe pas : {id_u=1}");
		$this->utilisateurController->detailAction();
	}
	

}