<?php

class CertificatTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var Certificat
     */
    private $certificat;
    /**
     * @var Certificat
     */
    private $bad_certificat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->certificat = new Certificat(file_get_contents(__DIR__ . "/fixtures/autorite-cert.pem"));
        $this->bad_certificat = new Certificat("toto");
    }

    public function testIsValid()
    {
        $this->assertTrue($this->certificat->isValid());
    }

    public function testIsNotValid()
    {
        $this->assertFalse($this->bad_certificat->isValid());
    }

    public function testGetContent()
    {
        $this->assertMatchesRegularExpression("#-----BEGIN CERTIFICATE-----#", $this->certificat->getContent());
    }

    public function testGetInfo()
    {
        $this->assertEquals("/C=FR/ST=France/L=Lyon/O=Sigmalis/CN=autorite developpement site s2low", $this->certificat->getInfo()['name']);
    }

    public function testGetVerifNumber()
    {
        $this->assertEquals("a609f02721467ed28a6d81609e79c42a", $this->certificat->getVerifNumber());
    }

    public function testGetVerifNumberFailed()
    {
        $this->assertFalse($this->bad_certificat->getVerifNumber());
    }

    public function testGetFancy()
    {
        $this->assertEquals(
            "/C=FR/ST=France/L=Lyon/O=Sigmalis/CN=autorite developpement site s2low",
            $this->certificat->getFancy()
        );
    }

    public function testGetFancyFailed()
    {
        $this->assertFalse($this->bad_certificat->getFancy());
    }

    public function testGetSerialNumber()
    {
        $this->assertEquals("C27E3178FBB5C372", $this->certificat->getSerialNumber());
    }

    public function testGetSerialNumberFailed()
    {
        $this->assertEmpty($this->bad_certificat->getSerialNumber());
    }

    public function testGetIssuer()
    {
        $this->assertEquals(
            "/C=FR/ST=France/L=Lyon/O=Sigmalis/CN=autorite developpement site s2low",
            $this->certificat->getIssuer()
        );
    }
}
