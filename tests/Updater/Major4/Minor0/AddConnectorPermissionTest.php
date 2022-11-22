<?php

namespace Pastell\Tests\Updater\Major4\Minor0;

use Exception;
use Pastell\Updater\Major4\Minor0\AddConnectorPermission;
use PastellTestCase;
use RoleDroit;
use RoleSQL;

class AddConnectorPermissionTest extends PastellTestCase
{
    /**
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

        $this->getObjectInstancier()->getInstance(AddConnectorPermission::class)->update();
        $droit = $roleSQL->getDroit($roleDroit->getAllDroit(), 'admin');
        $this->assertTrue($droit['connecteur:lecture']);
        $this->assertTrue($droit['connecteur:edition']);
    }
}
