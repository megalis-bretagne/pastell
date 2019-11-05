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
        $this->assertNull($this->saeConnecteur->getLastErrorCode());
    }
}
