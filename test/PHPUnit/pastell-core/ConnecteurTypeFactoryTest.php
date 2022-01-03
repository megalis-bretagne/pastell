<?php

use PHPUnit\Framework\TestCase;

class ConnecteurTypeFactoryTest extends TestCase
{
    /** @var  ConnecteurTypeFactory */
    private $connecteurTypeFactory;

    protected function setUp()
    {
        $extensions = $this->createMock("Extensions");
        $extensions
            ->method("getAllConnecteurType")
            ->willReturn(array("signature" => __DIR__ . "/fixtures/"));

        $objectInstancier  = new ObjectInstancier();
        $objectInstancier->{'Extensions'} = $extensions;
        $this->connecteurTypeFactory = new ConnecteurTypeFactory($objectInstancier);
    }

    /**
     * @throws RecoverableException
     */
    public function testGetActionExecutor()
    {
        $this->assertTrue($this->connecteurTypeFactory->getActionExecutor("signature", "SignatureEnvoieMock")->go());
    }

    /**
     * @throws RecoverableException
     */
    public function testConnecteurTypeNotFound()
    {
        $this->expectException("RecoverableException");
        $this->expectExceptionMessage("Impossible de trouver le connecteur type sae");
        $this->connecteurTypeFactory->getActionExecutor("sae", "SignatureEnvoieMock")->go();
    }

    /**
     * @throws RecoverableException
     */
    public function testClassNotFound()
    {
        $this->expectException(RecoverableException::class);
        $this->expectExceptionMessageRegExp("#Le fichier .*NotFoundMock.class.php n'a pas été trouvé#");
        $this->connecteurTypeFactory->getActionExecutor("signature", "NotFoundMock")->go();
    }

    public function testGetAllActionExecutor()
    {
        $this->assertEquals(array("SignatureEnvoieMock"), $this->connecteurTypeFactory->getAllActionExecutor());
    }
}
