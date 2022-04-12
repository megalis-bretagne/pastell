<?php

declare(strict_types=1);

namespace Pastell\Tests\Utilities;

use Pastell\Utilities\Certificate;
use PHPUnit\Framework\TestCase;

class CertificateTest extends TestCase
{
    private Certificate $certificat;
    private Certificate $badCertificat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->certificat = new Certificate(
            file_get_contents(__DIR__ . '/fixtures/autorite-cert.pem') ?: ''
        );
        $this->badCertificat = new Certificate('toto');
    }

    public function testIsValid(): void
    {
        static::assertTrue($this->certificat->isValid());
    }

    public function testIsNotValid(): void
    {
        static::assertFalse($this->badCertificat->isValid());
    }

    public function testGetContent(): void
    {
        static::assertMatchesRegularExpression(
            '#-----BEGIN CERTIFICATE-----#',
            $this->certificat->getContent()
        );
    }

    public function testGetInfo(): void
    {
        static::assertEquals(
            '/C=FR/ST=France/L=Lyon/O=Sigmalis/CN=autorite developpement site s2low',
            $this->certificat->getName()
        );
    }

    public function testGetVerifNumber(): void
    {
        static::assertEquals('a609f02721467ed28a6d81609e79c42a', $this->certificat->getMD5());
    }

    public function testGetVerifNumberFailed(): void
    {
        static::assertEmpty($this->badCertificat->getMD5());
    }

    public function testGetFancy(): void
    {
        static::assertEquals(
            '/C=FR/ST=France/L=Lyon/O=Sigmalis/CN=autorite developpement site s2low',
            $this->certificat->getName()
        );
    }

    public function testGetFancyFailed(): void
    {
        static::assertEmpty($this->badCertificat->getName());
    }

    public function testGetSerialNumber(): void
    {
        static::assertEquals('C27E3178FBB5C372', $this->certificat->getSerialNumber());
    }

    public function testGetSerialNumberFailed(): void
    {
        static::assertEmpty($this->badCertificat->getSerialNumber());
    }

    public function testGetIssuer(): void
    {
        static::assertEquals(
            '/C=FR/ST=France/L=Lyon/O=Sigmalis/CN=autorite developpement site s2low',
            $this->certificat->getIssuer()
        );
    }
}
