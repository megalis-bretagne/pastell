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
        $curlFunction->method('curl_setopt')->willReturnCallback(function ($curlHandle, $properties, $values) {
            if ($properties != CURLOPT_POSTFIELDS) {
                return;
            }
            $this->assertEquals(['foo' => 'bar'], $values);
        });

        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->addPostData("foo", "bar");

        $this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));
    }

    public function testPostFile()
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");
        $curlFunction->method('curl_setopt')->willReturnCallback(function ($curlHandle, $properties, $values) {
            if ($properties != CURLOPT_POSTFIELDS) {
                return;
            }
            $this->assertEquals("a.txt", $values['foo']->postname);
        });

        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->addPostFile("foo", __DIR__ . "/fixtures/a.txt", "a.txt");


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
        $curlFunction->method('curl_setopt')->willReturnCallback(function ($curlHandle, $properties, $values) {
            if ($properties != CURLOPT_POSTFIELDS) {
                return;
            }
            $this->assertRegExp("#foo.*bar.*foo.*baz#ms", $values);
        });
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
                if ($b !== CURLOPT_PROXY) {
                    $last_body = $c;
                }
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

    public function testKeepAttachmentOrder()
    {
        $curlFunction = $this->createMock(CurlFunctions::class);
        $curlFunction->method('curl_setopt')->willReturnCallback(function ($curlHandle, $properties, $values) {
            if ($properties != CURLOPT_POSTFIELDS) {
                return;
            }
            $this->assertRegExp("#aaa.*bbbb.*aaa#s", $values);
        });

        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->addPostFile("foo", __DIR__ . "/fixtures/a.txt", "a");
        $curlWrapper->addPostFile("foo", __DIR__ . "/fixtures/b.txt", "b");
        $curlWrapper->addPostFile("foo", __DIR__ . "/fixtures/a.txt", "a");
        $curlWrapper->get("url");
    }

    public function proxyDataProvider()
    {
        return [
            'proxy' => ["mon_proxy","mon_proxy","","url"],
            "proxy_url_in_no_proxy" => ["","mon_proxy","my_host","https://my_host:443/toto"],
            "no_proxy" => ["","","","url"]
        ];
    }

    /**
     * @dataProvider proxyDataProvider
     */
    public function testWithProxy(string $expected_setopt, string $proxy_value, string $no_proxy, string $url_called)
    {
        $curlFunction = $this->createMock("CurlFunctions");
        $curlFunction->method("curl_exec")->willReturn("OK");
        $curlFunction
            ->method("curl_setopt")
            ->willReturnCallback(function ($ch, $name, $value) use ($expected_setopt) {
                if ($name === CURLOPT_PROXY) {
                    $this->assertEquals($expected_setopt, $value);
                }
            });
        $curlWrapper = new CurlWrapper($curlFunction);
        $curlWrapper->setProxy($proxy_value);
        $curlWrapper->setNoProxy($no_proxy);
        $curlWrapper->get($url_called);
    }
}
