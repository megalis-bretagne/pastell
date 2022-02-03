<?php

namespace Pastell\Tests\Command\Connector;

use RoleSQL;
use RoleDroit;
use Pastell\Command\Connector\AddConnectorPermission;
use PastellTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class AddConnectorPermissionTest extends PastellTestCase
{
    public function testAddConnectorPermission()
    {
        $command = new AddConnectorPermission(
            $this->getObjectInstancier()->getInstance(RoleSQL::class),
            $this->getObjectInstancier()->getInstance(RoleDroit::class)
        );
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            ' // Nothing to do. There are already connector permission for role',
            $output
        );
    }
}
