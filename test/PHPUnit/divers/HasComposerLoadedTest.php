<?php

class HasComposerLoadedTest extends PHPUnit\Framework\TestCase
{
    public function testHasComposer()
    {
        $mille = new \phpseclib\Math\BigInteger("1000", 10);
        $this->assertEquals("1000", $mille);
    }
}
