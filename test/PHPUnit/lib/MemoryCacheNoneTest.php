<?php

use PHPUnit\Framework\TestCase;

class MemoryCacheNoneTest extends TestCase
{
    public function testFetch()
    {
        $memoryCacheNone = new MemoryCacheNone();
        $this->assertFalse($memoryCacheNone->fetch("foo"));
    }
}
