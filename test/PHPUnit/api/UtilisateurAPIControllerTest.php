<?php

class UtilisateurAPIControllerTest extends PastellTestCase
{
    public function testCreate()
    {
        $info = $this->getInternalAPI()->post(
            "utilisateur",
            [
                'email' => 'foo@bar.baz',
                'login' => 'foo',
                'password' => 'D@iw3DDf41Nl$DXzMJL!Uc2Yo',
                'nom' => 'foo',
                'prenom' => 'bar',
            ]
        );
        $this->assertEquals('foo', $info['nom']);
    }

    public function testCreateWithoutNom()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le nom est obligatoire");
        $this->getInternalAPI()->post(
            "utilisateur",
            [
                'email' => 'foo@bar.baz',
                'login' => 'foo',
                'password' => 'bar',
                'prenom' => 'bar',
            ]
        );
    }

    public function testCreateWithoutPrenom()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le prénom est obligatoire");
        $this->getInternalAPI()->post(
            "utilisateur",
            [
                'email' => 'foo@bar.baz',
                'login' => 'foo',
                'password' => 'bar',
                'nom' => 'bar',
            ]
        );
    }

    public function testCreateWithoutLogin(): void
    {
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage('Le login est obligatoire');
        $this->getInternalAPI()->post(
            '/utilisateur',
            [
                'email' => 'foo@bar.baz',
                'prenom' => 'foo',
                'password' => 'bar',
                'nom' => 'bar',
            ]
        );
    }

    public function testCreateWithoutEmail()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Votre adresse email ne semble pas valide");
        $this->getInternalAPI()->post(
            "utilisateur",
            [
                'login' => 'foo@bar.baz',
                'prenom' => 'foo',
                'password' => 'bar',
                'nom' => 'bar',
            ]
        );
    }

    public function testCreateSameUser()
    {
        $info =  [
            'login' => 'foo',
            'prenom' => 'foo',
            'password' => 'D@iw3DDf41Nl$DXzMJL!Uc2Yo',
            'nom' => 'bar',
            'email' => 'foo@bar.baz',
        ];
        $info = $this->getInternalAPI()->post("utilisateur", $info);
        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage("Un utilisateur avec le même login existe déjà.");
        $this->getInternalAPI()->patch("utilisateur/{$info['id_u']}", ['login' => 'admin']);
    }

    public function testSetCertificateKO()
    {
        $fileUploader = new FileUploaderMock();
        $fileUploader->setFiles(['certificat' => 'toto']);
        $this->getInternalAPI()->setFileUploader($fileUploader);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le certificat ne semble pas être valide");
        $this->getInternalAPI()->patch("utilisateur/1");
    }

    public function testSetCertificate()
    {
        $cert_content = file_get_contents(__DIR__ . "/../fixtures/autorite-cert.pem");
        $fileUploader = new FileUploaderMock();
        $fileUploader->setFiles(['certificat' => $cert_content]);
        $this->getInternalAPI()->setFileUploader($fileUploader);

        $info = $this->getInternalAPI()->patch("utilisateur/1");
        $this->assertEquals($cert_content, $info['certificat']);
    }


    public function testList()
    {
        $info = $this->getInternalAPI()->get("/utilisateur");
        $this->assertEquals('admin', $info[0]['login']);
    }

    public function testListV1()
    {
        $this->expectOutputRegex("#admin#");
        $this->getV1("list-utilisateur.php");
    }

    public function testDetail()
    {
        $info = $this->getInternalAPI()->get("/utilisateur/1");
        $this->assertEquals('admin', $info['login']);
    }

    public function testDetailNotExist()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("L'utilisateur n'existe pas : {id_u=42}");
        $this->getInternalAPI()->get("/utilisateur/42");
    }

    public function testDetailV1()
    {
        $this->expectOutputRegex("#Pommateau#");
        $this->getV1("detail-utilisateur.php?id_u=1");
    }

    public function testEdit()
    {
        $info = $this->getInternalAPI()->patch("utilisateur/1", ['login' => 'toto']);
        $this->assertEquals('toto', $info['login']);
    }

    public function testEditEntiteDeBase()
    {
        $info = $this->getInternalAPI()->patch("utilisateur/1", ['id_e' => '2']);
        $this->assertEquals(2, $info['id_e']);
        $info = $this->getInternalAPI()->patch("utilisateur/1", ['id_e' => '0']);
        $this->assertEquals(0, $info['id_e']);
        $info = $this->getInternalAPI()->patch("utilisateur/1", ['id_e' => '2']);
        $this->assertEquals(2, $info['id_e']);
        $info = $this->getInternalAPI()->patch("utilisateur/1", ['login' => 'toto']);
        $this->assertEquals(2, $info['id_e']);
    }

    public function testEditWithCreate()
    {
        $info = $this->getInternalAPI()->patch(
            "utilisateur",
            [
                'email' => 'foo@bar.baz',
                'login' => 'foo',
                'password' => 'D@iw3DDf41Nl$DXzMJL!Uc2Yo',
                'nom' => 'foo',
                'prenom' => 'bar',
                'create' => true
            ]
        );
        $this->assertEquals('foo', $info['login']);
    }

    public function testDelete()
    {
        $this->getInternalAPI()->delete("utilisateur/1");
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("L'utilisateur n'existe pas : {id_u=1}");
        $this->getInternalAPI()->get("utilisateur/1");
    }

    public function testPostActivateDeactivate(): void
    {
        $utilisateurActivated = $this->getInternalAPI()->get('utilisateur/2');
        $utilisateurDeactivated = $this->getInternalAPI()->post('/utilisateur/2/deactivate');
        $this->assertNotEquals($utilisateurActivated, $utilisateurDeactivated);

        $utilisateurReactivated = $this->getInternalAPI()->post('/utilisateur/2/activate');
        $this->assertEquals($utilisateurActivated, $utilisateurReactivated);
    }

    public function testPostDeactivateMyselfFail(): void
    {
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage('Vous ne pouvez pas désactiver votre compte utilisateur.');
        $this->getInternalAPI()->post('/utilisateur/1/deactivate');
    }

    public function testPostDeactivateFailDroit(): void
    {
        $this->getObjectInstancier()->getInstance(UtilisateurCreator::class)
            ->create('tester', 'tester', 'tester', 'tester@mail');
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Acces interdit id_e=0, droit=utilisateur:edition,id_u=3');
        $this->getInternalAPIAsUser('3')->post('/utilisateur/1/deactivate');
    }

    public function testCreateUserFail(): void
    {
        $this->getObjectInstancier()->getInstance(UtilisateurCreator::class)
            ->create('tester', 'tester', 'tester', 'tester@mail');
        $this->getObjectInstancier()->getInstance(RoleSQL::class)
            ->edit('utilisateurLectureEdition', 'Droit utilisateur');
        $this->getObjectInstancier()->getInstance(RoleSQL::class)
            ->addDroit('utilisateurLectureEdition', 'utilisateur:edition');
        $this->getObjectInstancier()->getInstance(RoleUtilisateur::class)
            ->addRole('3', 'utilisateurLectureEdition', '1');

        $userInfo = [
            'id_e' => '1',
            'login' => 'foo',
            'password' => 'D@iw3DDf41Nl$DXzMJL!Uc2Yo',
            'password2' => 'D@iw3DDf41Nl$DXzMJL!Uc2Yo',
            'nom' => 'baz',
            'prenom' => 'buz',
            'email' => 'boz@byz.fr'
        ];

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Acces interdit id_e=1, droit=utilisateur:creation,id_u=3');
        $this->getInternalAPIAsUser(3)->post('/utilisateur', $userInfo);
    }

    public function testPasswordPreservedPatchWithNoPassword(): void
    {
        $info =  [
            'login' => 'foo',
            'prenom' => 'foo',
            'password' => 'D@iw3DDf41Nl$DXzMJL!Uc2Yo',
            'nom' => 'bar',
            'email' => 'foo@bar.baz',
        ];
        $info = $this->getInternalAPI()->post("utilisateur", $info);
        $userCreated = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class)->getInfo($info['id_u']);
        $info2 = $this->getInternalAPI()->patch("utilisateur/3", ['nom' => 'faa']);
        $userUpdated = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class)->getInfo($info2['id_u']);
        self::assertSame($userCreated['password'], $userUpdated['password']);
    }
}
