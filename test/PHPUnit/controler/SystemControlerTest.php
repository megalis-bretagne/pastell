<?php

class SystemControlerTest extends ControlerTestCase
{
    /** @var  SystemControler */
    private $systemControler;

    protected function setUp()
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
        $this->getObjectInstancier()->setInstance(
            RedisWrapper::class,
            $this->createMock(RedisWrapper::class)
        );

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

    public function testEmptyCacheAction()
    {
        $redisWrapper = $this->createMock(RedisWrapper::class);
        $this->getObjectInstancier()->setInstance(RedisWrapper::class, $redisWrapper);
        $this->expectException(LastMessageException::class);
        $this->expectExceptionMessage("Le cache Redis a été vidé");
        $this->systemControler->emptyCacheAction();
    }
}
