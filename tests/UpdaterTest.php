<?php

namespace Pastell\Tests;

use Pastell\Updater;
use Pastell\UpdaterException;
use PastellTestCase;

class UpdaterTest extends PastellTestCase
{
    public function testUpdate()
    {
        $updater = $this->getObjectInstancier()->getInstance(Updater::class);
        $updater->update();
        $this->assertGreaterThan(1, count($this->getLogRecords()));
        $this->assertSame('Start script to 3.0.2', $this->getLogRecords()[0]['message']);
    }

    /**
     * @throws UpdaterException
     */
    public function testTo()
    {
        $updater = $this->getObjectInstancier()->getInstance(Updater::class);
        $updater->to('3.0.2');
        $this->assertCount(2, $this->getLogRecords());
        $this->assertSame('Start script to 3.0.2', $this->getLogRecords()[0]['message']);
    }

    /**
     * @throws UpdaterException
     */
    public function testToThrowsException()
    {
        $this->expectException(UpdaterException::class);
        $this->expectExceptionMessage('The update to version "not a version" does not exist');
        $updater = $this->getObjectInstancier()->getInstance(Updater::class);
        $updater->to('not a version');
    }
}
