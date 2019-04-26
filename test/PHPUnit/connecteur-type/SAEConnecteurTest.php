<?php

class SAEConnecteurTest extends PastellTestCase
{

    /**
     * @var SAEConnecteur $saeConnecteur
     */
    private $saeConnecteur;

    protected function setUp()
    {
        parent::setUp();

        $this->saeConnecteur = $this->getMockForAbstractClass(SAEConnecteur::class);
    }

    public function testGetLastErrorCode()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("This method is not implemented");

        $this->saeConnecteur->getLastErrorCode();
    }

}