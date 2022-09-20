<?php

namespace Pastell\Tests\Command;

use Pastell\Command\Bootstrap;
use Pastell\Command\FixYaml;
use Symfony\Component\Console\Tester\CommandTester;

class BootstrapTest extends \PastellTestCase
{
    public function testBootstrap(): void
    {
        $command = $this->getObjectInstancier()->getInstance(Bootstrap::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        self::assertNotEmpty($this->getLogRecords());
    }
}
