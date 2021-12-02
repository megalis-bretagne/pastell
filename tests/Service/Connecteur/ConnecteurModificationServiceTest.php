<?php

namespace Pastell\Tests\Service\Connecteur;

use Pastell\Service\Connecteur\ConnecteurActionService;
use PastellTestCase;

class ConnecteurModificationServiceTest extends PastellTestCase
{
    private function getConnecteurActionService(): ConnecteurActionService
    {
        return $this->getObjectInstancier()->getInstance(ConnecteurActionService::class);
    }

    public function testModifyConnectorByAPI()
    {
        $id_ce = $this->createConnector('test', "test")['id_ce'];
        $connecteur_action_message = $this->getConnecteurActionService()->getByIdCe($id_ce)[0]['message'];
        $this->assertEquals("Le connecteur test « test » a été créé", $connecteur_action_message);

        $result = $this->getInternalAPI()
            ->patch("/entite/1/connecteur/$id_ce/content/", ["champs1" => "valeur_champs1"]);
        $this->assertEquals('valeur_champs1', $result['data']['champs1']);
        $this->assertEquals('ok', $result['result']);

        $connecteurActionService = $this->getObjectInstancier()->getInstance(ConnecteurActionService::class);
        $connecteur_action_message = $connecteurActionService->getByIdCe($id_ce)[0]['message'];
        $this->assertEquals("Modification du connecteur via l'API", $connecteur_action_message);
    }

    public function testPostFileByAPI()
    {
        $this->getInternalAPI()->post('/entite/1/connecteur/12/file/champs5', [
            'file_name' => 'test.txt',
            'file_content' => 'test file content'
        ]);

        $connecteurActionService = $this->getObjectInstancier()->getInstance(ConnecteurActionService::class);
        $connecteur_action_message = $connecteurActionService->getByIdCe(12)[0]['message'];
        $this->assertEquals("Le fichier champs5 a été modifié via l'API", $connecteur_action_message);
    }

    public function testPatchExternalData()
    {
        $info = $this->getInternalAPI()->patch(
            "/entite/1/connecteur/12/externalData/external_data",
            array('choix' => 'foo')
        );
        $this->assertEquals('foo', $info['data']['external_data']);

        $connecteurActionService = $this->getObjectInstancier()->getInstance(ConnecteurActionService::class);
        $connecteur_action_message = $connecteurActionService->getByIdCe(12)[0]['message'];
        $this->assertEquals("L'external data external_data a été modifié via l'API", $connecteur_action_message);
    }
}
