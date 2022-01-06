<?php

class ConnexionControlerTest extends ControlerTestCase
{
    /**
     * @var ConnexionControler
     */
    private $connexionControler;

    protected function setUp()
    {
        parent::setUp();
        $this->connexionControler = $this->getControlerInstance(ConnexionControler::class);
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function testNotConnected()
    {
        $this->expectException(LastMessageException::class);
        $this->getObjectInstancier()->getInstance(Authentification::class)->deconnexion();
        $this->connexionControler->verifConnected();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function testConnexion()
    {
        $this->getObjectInstancier()->Authentification->Connexion('admin', 1);
        $this->assertTrue($this->connexionControler->verifConnected());
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function testConnexionAction()
    {
        $this->expectOutputRegex("#Veuillez saisir vos identifiants de connexion#");
        $this->connexionControler->connexionAction();
    }

    public function testConnexionAdminAction()
    {
        $this->expectOutputRegex("#Veuillez saisir vos identifiants de connexion#");
        $this->connexionControler->adminAction();
    }

    public function testOublieIdentifiant()
    {
        $this->expectOutputRegex("##");
        $this->connexionControler->oublieIdentifiantAction();
    }

    public function testChangementMdpAction()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Exit called with code 0");
        $this->expectOutputString("Le lien du mail a expiré. Veuillez recommencer la procédure");
        $this->connexionControler->changementMdpAction();
    }

    public function testChangementNoDroitAction()
    {
        $this->expectOutputRegex("##");
        $this->connexionControler->noDroitAction();
    }

    /**
     * @throws NotFoundException
     */
    public function testCasErrorAction()
    {
        $this->expectOutputRegex("#Erreur lors de la connexion au serveur distant#");
        $this->connexionControler->externalErrorAction();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function testLogoutAction()
    {
        $this->expectException(LastMessageException::class);
        $this->connexionControler->logoutAction();
    }

    public function testSessionLogout()
    {
        $this->assertTrue($this->getObjectInstancier()->getInstance(Authentification::class)->isConnected());
        $this->connexionControler->sessionLogoutAction();
        $this->assertFalse($this->getObjectInstancier()->getInstance(Authentification::class)->isConnected());
    }
}
