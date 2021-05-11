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
        $this->assertSame("40ad762180595a221360f07d3325a9ba7a96889477ed4afb02aae1afc12aa14c", $this->getConnecteurHashService()->getHash($id_ce));

        $connecteurConfig->addFileFromData(
            'definition',
            "definition.json",
            json_encode(["titre" => "Ceci est mon titre modifié"])
        );
        $this->assertSame("caa4ab3abc08a3a4e450b9ad87b505fa116ad0a58d303d82a6c74296f916f94a", $this->getConnecteurHashService()->getHash($id_ce));
    }
}
