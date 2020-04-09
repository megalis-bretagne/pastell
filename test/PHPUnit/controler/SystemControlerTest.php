<?php

class SystemControlerTest extends ControlerTestCase
{

    /** @var  SystemControler */
    private $systemControler;

    public function setUp()
    {
        parent::setUp();
        $this->systemControler = $this->getControlerInstance("SystemControler");
    }

    /**
     * @throws NotFoundException
     */
    public function testFluxDetailAction()
    {
        $this->expectOutputRegex("##");
        $this->systemControler->fluxDetailAction();
    }

    public function testIndex()
    {
        $this->expectOutputRegex("#Test du système#");
        $this->systemControler->indexAction();
    }

    /**
     * @throws NotFoundException
     */
    public function testListManquant()
    {
        $this->expectOutputRegex('#SEDA Standard#');
        $this->systemControler->missingConnecteurAction();
    }

    /**
     * @throws Exception
     */
    public function testExportAllMissingConnecteurAction()
    {
        $this->expectOutputRegex("#Content-type: application/zip#");
        $this->systemControler->exportAllMissingConnecteurAction();
    }
}
