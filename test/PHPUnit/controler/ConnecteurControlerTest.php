<?php

class ConnecteurControlerTest extends ControlerTestCase
{

    /**
     * @var ConnecteurControler
     */
    private $connecteurControler;

    protected function setUp()
    {
        parent::setUp();
        $this->connecteurControler = $this->getControlerInstance("ConnecteurControler");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testEditionActionConnecteurDoesNotExists()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Ce connecteur n'existe pas");
        $this->connecteurControler->editionAction();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testEditionAction()
    {
        $this->setGetInfo(['id_ce' => 11]);
        $this->expectOutputRegex("#Connecteur mailsec - mailsec : Mail securise#");
        $this->connecteurControler->editionAction();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testEditionLibelleAction()
    {
        $this->setGetInfo(['id_ce' => 11]);
        $this->expectOutputRegex("#Connecteur mailsec - mailsec : Mail securise#");
        $this->connecteurControler->editionLibelleAction();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function testDoEditionLibelleFailed()
    {
        $this->expectException(LastErrorException::class);
        $this->expectExceptionMessage("Ce connecteur n'existe pas");
        $this->connecteurControler->doEditionLibelleAction();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testEditionActionWhenConnecteurDefinitionDoesNotExists()
    {
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance(ConnecteurEntiteSQL::class);
        $id_ce = $connecteurEntiteSQL->addConnecteur(
            1,
            "not_existing_connecteur",
            "signature",
            "foo"
        );
        $this->setGetInfo(['id_ce' => $id_ce]);
        $this->expectOutputRegex(
            "#Impossible d'afficher les propriétés du connecteur car celui-ci est inconnu sur cette plateforme Pastell#"
        );
        $this->connecteurControler->editionAction();
    }

    public function testWithAPI()
    {
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance(ConnecteurEntiteSQL::class);
        $id_ce = $connecteurEntiteSQL->addConnecteur(
            1,
            "not_existing_connecteur",
            "signature",
            "foo"
        );
        $result = $this->getInternalAPI()->patch("/entite/1/connecteur/$id_ce/content/", ["foo" => "bar"]);
        $this->assertEquals('foo', $result['libelle']);
        $this->assertEquals('ok', $result['result']);
    }
}
