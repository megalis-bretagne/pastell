<?php

namespace Service;

use Pastell\Service\PasswordEntropy;
use PHPUnit\Framework\TestCase;

class PasswordEntropyTest extends TestCase
{
    public function testGetEntropyForDisplay()
    {
        $passwordEntropy = new PasswordEntropy(10);
        $this->assertEquals(10, $passwordEntropy->getEntropyForDisplay());
    }

    public function testGetEntropyForDisplayWithDefault()
    {
        $passwordEntropy = new PasswordEntropy(0);
        $this->assertEquals(80, $passwordEntropy->getEntropyForDisplay());
    }

    public function testIsPasswordStrongEnough()
    {
        $passwordEntropy = new PasswordEntropy(80);
        $this->assertFalse($passwordEntropy->isPasswordStrongEnough("Tr0ub4dor&3"));
        $this->assertTrue($passwordEntropy->isPasswordStrongEnough("correct-horse-battery-staple"));
    }

    public function testIsPasswordStrongWithDefault()
    {
        $passwordEntropy = new PasswordEntropy(0);
        $this->assertTrue($passwordEntropy->isPasswordStrongEnough("Tr0ub4dor&3"));
    }
}
