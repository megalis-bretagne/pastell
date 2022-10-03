<?php

class DaemonManagerTest extends PHPUnit\Framework\TestCase
{
    /** @var  DaemonManager */
    private $daemonManager;

    protected function setUp(): void
    {
        $this->daemonManager = new DaemonManager();
    }

    public function testStatus()
    {
        $this->daemonManager->stop();
        $this->assertEquals(DaemonManager::IS_STOPPED, $this->daemonManager->status());
    }
}
