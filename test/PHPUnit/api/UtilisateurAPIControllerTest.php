<?php

class UtilisateurAPIControllerTest extends PastellTestCase {


	public function testCreate(){
		$info = $this->getInternalAPI()->post("utilisateur",
			array (
				'email'=>'foo@bar.baz',
				'login'=>'foo',
				'password' => 'bar',
				'nom' => 'foo',
				'prenom' => 'bar',
			)
		);
		$this->assertEquals('foo',$info['nom']);
	}

	public function testCreateWithoutNom(){
		$this->setExpectedException("Exception","Le nom est obligatoire");
		$this->getInternalAPI()->post("utilisateur",
			array (
				'email'=>'foo@bar.baz',
				'login'=>'foo',
				'password' => 'bar',
				'prenom' => 'bar',
			)
		);
	}

	public function testCreateWithoutPrenom(){
		$this->setExpectedException("Exception","Le prénom est obligatoire");
		$this->getInternalAPI()->post("utilisateur",
			array (
				'email'=>'foo@bar.baz',
				'login'=>'foo',
				'password' => 'bar',
				'nom' => 'bar',
			)
		);
	}

	public function testCreateWithoutLogin(){
		$this->setExpectedException("Exception","Il faut saisir un login");
		$this->getInternalAPI()->post("utilisateur",
			array (
				'email'=>'foo@bar.baz',
				'prenom'=>'foo',
				'password' => 'bar',
				'nom' => 'bar',
			)
		);
	}

	public function testCreateWithoutEmail(){
		$this->setExpectedException("Exception","Votre adresse email ne semble pas valide");
		$this->getInternalAPI()->post("utilisateur",
			array (
				'login'=>'foo@bar.baz',
				'prenom'=>'foo',
				'password' => 'bar',
				'nom' => 'bar',
			)
		);
	}

	public function testCreateSameUser(){
		$info = array (
			'login'=>'foo',
			'prenom'=>'foo',
			'password' => 'bar',
			'nom' => 'bar',
			'email'=>'foo@bar.baz',
		);
		$info = $this->getInternalAPI()->post("utilisateur",$info);
		$this->setExpectedException("ConflictException","Un utilisateur avec le même login existe déjà.");
		$this->getInternalAPI()->patch("utilisateur/{$info['id_u']}",array('login'=>'admin'));
	}

	public function testSetCertificateKO(){
		$fileUploader = new FileUploaderMock();
		$fileUploader->setFiles(array('certificat'=>'toto'));
		$this->getInternalAPI()->setFileUploader($fileUploader);
		$this->setExpectedException("Exception","Le certificat ne semble pas être valide");
		$this->getInternalAPI()->patch("utilisateur/1");
	}

	public function testSetCertificate(){
		$cert_content = file_get_contents(__DIR__."/../fixtures/autorite-cert.pem");
		$fileUploader = new FileUploaderMock();
		$fileUploader->setFiles(array('certificat'=>$cert_content));
		$this->getInternalAPI()->setFileUploader($fileUploader);

		$info =$this->getInternalAPI()->patch("utilisateur/1");
		$this->assertEquals($cert_content,$info['certificat']);
	}


	public function testList(){
		$info = $this->getInternalAPI()->get("/utilisateur");
		$this->assertEquals('admin',$info[0]['login']);
	}

	public function testListV1(){
		$this->expectOutputRegex("#admin#");
		$this->getV1("list-utilisateur.php");
	}

	public function testDetail(){
		$info = $this->getInternalAPI()->get("/utilisateur/1");
		$this->assertEquals('admin',$info['login']);
	}

	public function testDetailNotExist(){
		$this->setExpectedException("Exception","L'utilisateur n'existe pas : {id_u=42}");
		$this->getInternalAPI()->get("/utilisateur/42");
	}

	public function testDetailV1(){
		$this->getV1("detail-utilisateur.php?id_u=1");
	}

	public function testEdit(){
		$info = $this->getInternalAPI()->patch("utilisateur/1",array('login'=>'toto'));
		$this->assertEquals('toto',$info['login']);
	}

	public function testEditWithCreate(){
		$info = $this->getInternalAPI()->patch("utilisateur",
			array (
				'email'=>'foo@bar.baz',
				'login'=>'foo',
				'password' => 'bar',
				'nom' => 'foo',
				'prenom' => 'bar',
				'create'=>true
			)
		);
		$this->assertEquals('foo',$info['login']);
	}


	public function testDelete(){
		$this->getInternalAPI()->delete("utilisateur/1");
		$this->setExpectedException("Exception","L'utilisateur n'existe pas : {id_u=1}");
		$this->getInternalAPI()->get("utilisateur/1");
	}
	

}