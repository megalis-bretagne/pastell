<?php

class FreeSpaceTest extends PHPUnit\Framework\TestCase
{
    public function testGetFreeSpace()
    {
        $freeSpace = new FreeSpace();
        $data = $freeSpace->getFreeSpace(__DIR__);
        $this->assertArrayHasKey('disk_use_percent', $data);
    }
}
