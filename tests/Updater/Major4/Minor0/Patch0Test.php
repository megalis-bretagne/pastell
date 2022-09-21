<?php

namespace Pastell\Tests\Updater\Major4\Minor0;

use Exception;
use NotFoundException;
use Pastell\Updater\Major4\Minor0\Patch0;
use PastellTestCase;
use RoleDroit;
use RoleSQL;
use UnrecoverableException;

use function PHPUnit\Framework\assertTrue;

class Patch0Test extends PastellTestCase
{
    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testAddConnectorPermission()
    {
        $roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $roleDroit = $this->getObjectInstancier()->getInstance(RoleDroit::class);

        $droit = $roleSQL->getDroit($roleDroit->getAllDroit(), 'admin');
        $this->assertTrue($droit['connecteur:lecture']);
        $this->assertTrue($droit['connecteur:edition']);

        unset($droit['connecteur:lecture']);
        unset($droit['connecteur:edition']);
        $roleSQL->updateDroit('admin', array_keys($droit, true));

        $droit = $roleSQL->getDroit($roleDroit->getAllDroit(), 'admin');
        $this->assertFalse($droit['connecteur:lecture']);
        $this->assertFalse($droit['connecteur:edition']);

        $this->getObjectInstancier()->getInstance(Patch0::class)->update();
        $droit = $roleSQL->getDroit($roleDroit->getAllDroit(), 'admin');
        $this->assertTrue($droit['connecteur:lecture']);
        $this->assertTrue($droit['connecteur:edition']);
    }
}
