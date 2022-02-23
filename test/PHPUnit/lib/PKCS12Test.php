<?php

class PKCS12Test extends PHPUnit\Framework\TestCase
{
    /**
     * @var PKCS12
     */
    private $pkcs12;
    private $p12_file_path;
    private $p12_password;
    private $certificate_name;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pkcs12 = new PKCS12();
        $this->p12_file_path = __DIR__ . "/fixtures/robert_petitpoids.p12";
        $this->p12_password = "robert_petitpoids";
        $this->certificate_name = "/C=FR/ST=France/L=Lyon/O=Sigmalis/OU=sigmalis/CN=robert_petitpoids";
    }

    public function testGetAll()
    {
        $all = $this->pkcs12->getAll($this->p12_file_path, $this->p12_password);
        $info = openssl_x509_parse($all['cert']);
        $this->assertEquals($this->certificate_name, $info['name']);
        openssl_x509_check_private_key($all['cert'], $all['pkey']);
    }

    public function testNotExists()
    {
        $this->assertFalse($this->pkcs12->getAll('foo', 'bar'));
    }

    public function testBasPassword()
    {
        $this->assertFalse($this->pkcs12->getAll($this->p12_file_path, "bad password"));
    }

    public function testUnencryptedKey()
    {
        $result = $this->pkcs12->getUnencryptedKey($this->p12_file_path, $this->p12_password);
        $this->assertRegExp("#-----BEGIN PRIVATE KEY-----#", $result);
    }
}
