<?php

namespace Pastell\Tests\Command\Connector;

use ConnecteurEntiteSQL;
use Pastell\Command\Connector\DeleteByType;
use Pastell\Service\Connecteur\ConnecteurDeletionService;
use PastellTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class DeleteByTypeTest extends PastellTestCase
{

    public function testCommand(): void
    {
        $connectorEntiteSql = $this->getObjectInstancier()->getInstance(ConnecteurEntiteSQL::class);

        $command = new DeleteByType(
            $connectorEntiteSql,
            $this->getObjectInstancier()->getInstance(ConnecteurDeletionService::class)
        );

        $commandTester = new CommandTester($command);

        $commandTester->execute(
            [
                'type' => 'test',
            ],
            [
                'interactive' => false,
            ]
        );

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('0/2', $output);
        $this->assertStringContainsString('2/2', $output);

        $this->assertCount(0, $connectorEntiteSql->getAllById('test'));
    }
}
