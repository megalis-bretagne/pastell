<?php

namespace Pastell\Tests\Command\Module;

use FluxEntiteSQL;
use Pastell\Command\Module\CopyAssociations;
use Pastell\Service\Connecteur\ConnecteurAssociationService;
use PastellTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CopyAssociationsTest extends PastellTestCase
{

    public function testCopyActesGeneriqueToAuto()
    {
        $command = new CopyAssociations(
            $this->getObjectInstancier()->getInstance(ConnecteurAssociationService::class),
            $this->getObjectInstancier()->getInstance(FluxEntiteSQL::class)
        );
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);

        $commandTester->execute([
            'source' => 'actes-generique',
            'target' => 'actes-automatique'
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('0/5', $output);
        $this->assertStringContainsString('5/5', $output);
    }
}
