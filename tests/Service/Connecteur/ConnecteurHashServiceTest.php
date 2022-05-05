<?php

namespace Pastell\Tests\Service\Connecteur;

use Exception;
use Pastell\Service\Connecteur\ConnecteurHashService;
use PastellTestCase;

class ConnecteurHashServiceTest extends PastellTestCase
{
    private function getConnecteurHashService()
    {
        return $this->getObjectInstancier()->getInstance(ConnecteurHashService::class);
    }

    /**
     * @throws Exception
     */
    public function testGetHash()
    {
        $id_ce = $this->createConnector('test', 'test 2', 1)['id_ce'];

        $this->configureConnector($id_ce, [
            'champs1' => 'ma valeur',
        ]);
        $this->assertSame("9b7f6dde 564ad188 50fd0701 8afc6c70 8b830342 07c0e5d0 63d4feee 67fcbe7c", $this->getConnecteurHashService()->getHash($id_ce));

        $this->configureConnector($id_ce, [
            'champs1' => 'ma valeur modifié',
        ]);
        $this->assertSame("c2682dde ad30e147 07256056 0b01f1a7 2be17538 8c68ea2e f36b3dbc 5519802c", $this->getConnecteurHashService()->getHash($id_ce));
    }
}
