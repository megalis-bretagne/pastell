<?php

class OpensslTSWrapperTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var OpensslTSWrapper
     */
    private $opensslTSWrapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->opensslTSWrapper = new OpensslTSWrapper(OPENSSL_PATH);
    }


    public function testGetTimestampQuery()
    {
        $timestamp_query = $this->opensslTSWrapper->getTimestampQuery("toto");
        $this->assertNotEmpty($timestamp_query);
    }

    public function testGetTimestampQueryString()
    {
        $this->opensslTSWrapper->setHashAlgorithm('sha256');
        $timestamp_query = $this->opensslTSWrapper->getTimestampQuery("toto");
        $timestamp_query_string = $this->opensslTSWrapper->getTimestampQueryString($timestamp_query);
        $this->assertRegExp("#Hash Algorithm: sha256#", $timestamp_query_string);
    }

    public function testCreateTimestampReply()
    {
        $timestamp_query = $this->opensslTSWrapper->getTimestampQuery("toto");
        chdir(__DIR__ . "/fixtures/");
        $timestamp_reply = $this->opensslTSWrapper->createTimestampReply($timestamp_query, __DIR__ . "/fixtures/timestamp-cert.pem", __DIR__ . "/fixtures/timestamp-key.pem", "", __DIR__ . "/fixtures/openssl-tsa.cnf");
        $timestamp_reply_string = $this->opensslTSWrapper->getTimestampReplyString($timestamp_reply);
        $this->assertRegExp("#Policy OID: 1.2.3.4.1#", $timestamp_reply_string);
    }

    public function testVerify()
    {
        $this->opensslTSWrapper->setHashAlgorithm('sha256');
        $timestamp_query = $this->opensslTSWrapper->getTimestampQuery("toto");
        chdir(__DIR__ . "/fixtures/");
        $timestamp_reply = $this->opensslTSWrapper->createTimestampReply($timestamp_query, __DIR__ . "/fixtures/timestamp-cert.pem", __DIR__ . "/fixtures/timestamp-key.pem", "", __DIR__ . "/fixtures/openssl-tsa.cnf");
        $this->assertTrue($this->opensslTSWrapper->verify("toto", $timestamp_reply, __DIR__ . "/fixtures/autorite-cert.pem", __DIR__ . "/fixtures/timestamp-cert.pem", __DIR__ . "/fixtures/openssl-tsa.cnf"));
    }

    public function testVerifyFailed()
    {
        $timestamp_reply = "not a timestamp reply";
        $this->assertFalse($this->opensslTSWrapper->verify("toto", $timestamp_reply, __DIR__ . "/fixtures/autorite-cert.pem", __DIR__ . "/fixtures/timestamp-cert.pem", __DIR__ . "/fixtures/openssl-tsa.cnf"));
        $this->assertRegExp("#Verification: FAILED#", $this->opensslTSWrapper->getLastError());
    }

    public function testAllSha1()
    {
        $timestamp_query = $this->opensslTSWrapper->getTimestampQuery("toto");
        chdir(__DIR__ . "/fixtures/");
        $timestamp_reply = $this->opensslTSWrapper->createTimestampReply($timestamp_query, __DIR__ . "/fixtures/timestamp-cert.pem", __DIR__ . "/fixtures/timestamp-key.pem", "", __DIR__ . "/fixtures/openssl-tsa.cnf");
        $this->assertTrue($this->opensslTSWrapper->verify("toto", $timestamp_reply, __DIR__ . "/fixtures/autorite-cert.pem", __DIR__ . "/fixtures/timestamp-cert.pem", __DIR__ . "/fixtures/openssl-tsa.cnf"));
    }
}
