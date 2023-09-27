<?php

namespace Pastell\Tests\Command\Connector;

use ConnecteurEntiteSQL;
use Pastell\Command\Connector\Dissociate;
use Pastell\Service\Connecteur\ConnecteurAssociationService;
use PastellTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DissociateTest extends PastellTestCase
{
    /**
     * @var CommandTester
     */
    private CommandTester $commandTester;
    protected function setUp(): void
    {
        parent::setUp();
        $this->createConnector('test', 'Test global', 0);
        $command = new Dissociate(
            $this->getObjectInstancier()->getInstance(ConnecteurEntiteSQL::class),
            $this->getObjectInstancier()->getInstance(ConnecteurAssociationService::class)
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
        static::assertStringContainsString('Global connector not found', $output);
    }

    public function testCommandNoConfirmation(): void
    {
        static::assertSame(
            1,
            $this->executeCommand('test', 'no')
        );
    }
}
