<?php

declare(strict_types=1);

namespace Pastell\Tests\Command\Connector;

use ConnecteurEntiteSQL;
use Pastell\Command\Connector\Dissociate;
use Pastell\Service\Connecteur\ConnecteurAssociationService;
use PastellTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DissociateTest extends PastellTestCase
{
    private CommandTester $commandTester;

    /**
     * @throws \UnrecoverableException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $connecteur_test = $this->createConnector('test', 'Test global', 0);
        $this->getObjectInstancier()->getInstance(ConnecteurAssociationService::class)
            ->addConnecteurAssociation(
                0,
                (int)$connecteur_test['id_ce'],
                'test'
            );
        $command = new Dissociate(
            $this->getObjectInstancier()->getInstance(ConnecteurAssociationService::class),
            $this->getObjectInstancier()
        );
        $this->commandTester = new CommandTester($command);
    }

    private function executeCommand(string $type, string $confirm): int
    {
        $this->commandTester->setInputs([$confirm]);
        return $this->commandTester->execute(
            [
                'type' => $type
            ]
        );
    }

    public function testCommand(): void
    {
        static::assertSame(
            0,
            $this->executeCommand('test', 'yes')
        );
        $output = $this->commandTester->getDisplay();
        static::assertStringContainsString('Successfully dissociated connector', $output);
    }

    public function testCommandUnknownType(): void
    {
        $this->executeCommand('toto', 'yes');
        $output = $this->commandTester->getDisplay();
        static::assertStringContainsString('Connector type not found', $output);
    }

    public function testCommandNoAssociation(): void
    {
        $this->executeCommand('visionneuse_pes', 'yes');
        $output = $this->commandTester->getDisplay();
        static::assertStringContainsString('No global connector associated to this type ', $output);
    }

    public function testCommandNoConfirmation(): void
    {
        static::assertSame(
            1,
            $this->executeCommand('test', 'no')
        );
    }
}
