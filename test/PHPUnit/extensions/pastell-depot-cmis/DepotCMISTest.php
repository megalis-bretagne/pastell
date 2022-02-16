<?php

final class DepotCMISTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testExtensionIsScoped(): void
    {
        $this->getObjectInstancier()->getInstance(Extensions::class)->autoloadExtensions();
        $connector = $this->createConnector('depot-cmis', 'test');
        /** @var DepotCMIS $class */
        $class = $this->getConnecteurFactory()->getConnecteurById($connector['id_ce']);
        $this->assertInstanceOf(PastellExtension\PastellDepotCmis\GuzzleHttp\Client::class, $class->getClient());
    }
}
