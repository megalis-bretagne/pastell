<?php

class CurlWrapperTest extends PHPUnit\Framework\TestCase
{

    public function testGet()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");
        $curlFunction->method("curl_getinfo")->willReturn("200");

        $curlWrapper = new CurlWrapper($curlFunction);

        $curlWrapper->httpAuthentication("eric", "mdp");
        $curlWrapper->addHeader("key", "value");
        $curlWrapper->setAccept("format");
        $curlWrapper->dontVerifySSLCACert();
        $curlWrapper->setClientCertificate("x", "y", "z");
        $curlWrapper->setServerCertificate("y");

        $this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));
        $this->assertEquals("OK", $curlWrapper->getLastOutput());
        $this->assertEquals("200", $curlWrapper->getLastHttpCode());
    }

    public function testGetFailed()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_error")->willReturn("Connexion impossible");
        $curlWrapper = new CurlWrapper($curlFunction);

        $this->assertFalse($curlWrapper->get("http://pastell.adullact.org"));
        $this->assertEquals("Erreur de connexion au serveur : Connexion impossible", $curlWrapper->getLastError());
    }


    public function testGet404()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_getinfo")->willReturn("404");
        $curlFunction->method("curl_exec")->willReturn("not found");
        $curlWrapper = new CurlWrapper($curlFunction);

        $this->assertFalse($curlWrapper->get("http://pastell.adullact.org"));
        $this->assertEquals("http://pastell.adullact.org : 404 Not Found", $curlWrapper->getLastError());
        $this->assertEquals("not found", $curlWrapper->getLastOutput());
    }

    public function testPost()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");

        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->addPostData("foo", "bar");

        $this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));
    }

    public function testPostFile()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");

        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->addPostFile("foo", __FILE__);

        $this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));
    }

    public function testPostURLEncode()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");

        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->setPostDataUrlEncode(array("foo" => "bar"));

        $this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));
    }

    public function testPostDataWithSimilarName()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");

        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->addPostData("foo", "bar");
        $curlWrapper->addPostData("foo", "baz");

        $this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));
    }

    public function testPostDataWithSimilarFilename()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");

        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->addPostFile("foo", __FILE__, false, "text/plain", "binary");
        $curlWrapper->addPostFile("foo", __DIR__ . "/fixtures/autorite-cert.pem");

        $this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));
    }

    public function testPostSameFile()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");

        $last_body = "";
        $curlFunction
            ->method("curl_setopt")
            ->willReturnCallback(function ($a, $b, $c) use (&$last_body) {
                $last_body = $c;
            });

        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->addPostFile("foo", __DIR__ . "/fixtures/autorite-cert.pem");
        $curlWrapper->addPostFile("foo", __DIR__ . "/fixtures/autorite-cert.pem");

        $this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));

        $this->assertRegExp("#foo.*foo#ms", $last_body);
    }



    public function testGetHTTPCode()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");
        $curlFunction->method("curl_getinfo")->willReturn("403");
        $curlWrapper = new CurlWrapper($curlFunction);

        $this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));
        $this->assertEquals("403", $curlWrapper->getHTTPCode());
    }

    public function testConstruct()
    {
        $curlWrapper = new CurlWrapper();
        $this->assertInstanceOf(CurlWrapper::class, $curlWrapper);
    }


    public function testsetJsonPostData()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");
        $curlFunction->method("curl_getinfo")->willReturn("200");

        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->setJsonPostData(['foo' => 'bar','baz' => 'buz']);
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");
        $this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));
    }
}
