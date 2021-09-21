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
        $this->assertSame("26604207 3ac7d7d8 5c40df76 315ec5d5 b07c2a6e 01965716 85cf449d 354e1f55", $this->getConnecteurHashService()->getHash($id_ce));

        $this->configureConnector($id_ce, [
            'champs1' => 'ma valeur modifié',
        ]);
        $this->assertSame("a595fdf5 28b58771 6929ffd4 8a17e79e 00b4abd6 b785fb63 f7b8af88 c192abb4", $this->getConnecteurHashService()->getHash($id_ce));
    }
}
