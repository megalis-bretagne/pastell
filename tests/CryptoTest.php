<?php

namespace Pastell\Tests;

use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\Alerts\InvalidDigestLength;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\Halite\Alerts\InvalidSalt;
use ParagonIE\Halite\Alerts\InvalidSignature;
use ParagonIE\Halite\Alerts\InvalidType;
use Pastell\Crypto;
use PHPUnit\Framework\TestCase;

class CryptoTest extends TestCase
{

    /**
     * @throws CannotPerformOperation
     * @throws InvalidDigestLength
     * @throws InvalidKey
     * @throws InvalidMessage
     * @throws InvalidSalt
     * @throws InvalidSignature
     * @throws InvalidType
     */
    public function testEncryptDecrypt(): void
    {
        $clearText = 'my clear message to encrypt';
        $password = 'the password';
        $crypto = new Crypto();
        $encrypted = $crypto->encrypt($clearText, $password);
        $this->assertSame($clearText, $crypto->decrypt($encrypted, $password));
    }
}
