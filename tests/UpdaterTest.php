<?php

namespace Pastell\Tests;

use PastellTestCase;
use Pastell\Updater;

class UpdaterTest extends PastellTestCase
{
    public function testUpdate()
    {
        $updater = $this->getObjectInstancier()->getInstance(Updater::class);
        $updater->update();
        $this->assertGreaterThan(1, count($this->getLogRecords()));
        $this->assertSame(
            'Start Migrate Pastell\Updater\Major4\Minor0\AddConnectorPermission',
            $this->getLogRecords()[1]['message']
        );
    }
}
