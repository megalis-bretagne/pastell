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

        /*
         * FIXME : Guzzle5 émet un deprecated...
         * La seule solution est d'attendre que la lib dkd/php-cmis soit mise à jour ou de passer à autre chose
         * https://packagist.org/packages/dkd/php-cmis
         */
        @ $client = $class->getClient();

        $this->assertInstanceOf(PastellExtension\PastellDepotCmis\GuzzleHttp\Client::class, $client);
    }
}
