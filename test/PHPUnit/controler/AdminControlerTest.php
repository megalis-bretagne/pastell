<?php

class AdminControlerTest extends ControlerTestCase
{
    private AdminControler $adminControler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminControler = $this->getControlerInstance(AdminControler::class);
    }

    public function testCreateAdmin()
    {
        $this->assertTrue($this->adminControler->createAdmin('admin2', 'D@iw3DDf41Nl$DXzMJL!Uc2Yo', 'admin@sigmalis.com'));
    }

    public function testCreateAdminFail()
    {
        $this->assertFalse($this->adminControler->createAdmin('admin', 'admin', 'admin@sigmalis.com'));
    }

    public function testCreateOrUpdateAdmin(): void
    {
        $email = 'foo@bar.com';
        $login = 'foo';
        $this->adminControler->createOrUpdateAdmin($login, $email);
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        $id_u = $utilisateurSQL->getIdFromLogin('foo');
        self::assertEquals('foo', $utilisateurSQL->getInfo($id_u)['login']);
        self::assertMatchesRegularExpression("/Mot de passe de l'administrateur : (.*)/", $this->getLogRecords()[2]['message']);
    }
}
