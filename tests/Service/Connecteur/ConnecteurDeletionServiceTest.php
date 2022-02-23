<?php

namespace Pastell\Tests\Service\Connecteur;

use ConnecteurEntiteSQL;
use Exception;
use Pastell\Service\Connecteur\ConnecteurDeletionService;
use PastellTestCase;

final class ConnecteurDeletionServiceTest extends PastellTestCase
{
    /**
     * @var ConnecteurDeletionService
     */
    private $connectorDeletionService;
    /**
     * @var ConnecteurEntiteSQL
     */
    private $connectorEntitySql;

    protected function setUp(): void
    {
        $this->connectorDeletionService = $this->getObjectInstancier()->getInstance(ConnecteurDeletionService::class);
        $this->connectorEntitySql = $this->getObjectInstancier()->getInstance(ConnecteurEntiteSQL::class);
        parent::setUp();
    }

    /**
     * @throws Exception
     */
    public function testRemoveOneDisassociatedConnector(): void
    {
        $testConnectors = $this->connectorEntitySql->getAllByConnecteurId('test');
        $this->assertCount(2, $testConnectors);
        $this->connectorDeletionService->deleteConnecteur($testConnectors[0]['id_ce']);

        $testConnectors = $this->connectorEntitySql->getAllByConnecteurId('test');
        $this->assertCount(1, $testConnectors);
    }

    public function testRemoveAssociatedConnector(): void
    {
        $testConnectors = $this->connectorEntitySql->getAllByConnecteurId('test');
        $this->assertCount(2, $testConnectors);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Ce connecteur est utilisé par des flux :  test');
        $this->connectorDeletionService->deleteConnecteur($testConnectors[1]['id_ce']);
    }

    /**
     * @throws Exception
     */
    public function testDisassociateAndRemoveConnector(): void
    {
        $testConnectors = $this->connectorEntitySql->getAllByConnecteurId('test');
        $this->assertCount(2, $testConnectors);
        $connectorId = $testConnectors[1]['id_ce'];
        $this->connectorDeletionService->disassociate($connectorId);
        $this->connectorDeletionService->deleteConnecteur($connectorId);

        $testConnectors = $this->connectorEntitySql->getAllByConnecteurId('test');
        $this->assertCount(1, $testConnectors);
    }
}
