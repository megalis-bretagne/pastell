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
        $id_ce = $this->createConnector(
            'transformation-generique',
            'Transformation generique'
        )['id_ce'];

        $connecteurConfig = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);

        $connecteurConfig->addFileFromData(
            'definition',
            "definition.json",
            json_encode(["titre" => "Ceci est mon titre"])
        );
        $this->assertSame("40ad7621 80595a22 1360f07d 3325a9ba 7a968894 77ed4afb 02aae1af c12aa14c", $this->getConnecteurHashService()->getHash($id_ce));

        $connecteurConfig->addFileFromData(
            'definition',
            "definition.json",
            json_encode(["titre" => "Ceci est mon titre modifié"])
        );
        $this->assertSame("caa4ab3a bc08a3a4 e450b9ad 87b505fa 116ad0a5 8d303d82 a6c74296 f916f94a", $this->getConnecteurHashService()->getHash($id_ce));
    }
}
