<?php

class CurlWrapperTest extends PHPUnit\Framework\TestCase {

	public function testGet() {
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_exec")->willReturn("OK");
		$curlFunction->expects($this->any())->method("curl_getinfo")->willReturn("200");

		$curlWrapper = new CurlWrapper($curlFunction);

		$curlWrapper->httpAuthentication("eric","mdp");
		$curlWrapper->addHeader("key","value");
		$curlWrapper->setAccept("format");
		$curlWrapper->dontVerifySSLCACert();
		$curlWrapper->setClientCertificate("x","y","z");
		$curlWrapper->setServerCertificate("y");

		$this->assertEquals("OK", $curlWrapper->get("http://pastell.adullact.org"));
		$this->assertEquals("OK",$curlWrapper->getLastOutput());
		$this->assertEquals("200",$curlWrapper->getLastHttpCode());
	}

	public function testGetFailed(){
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_error")->willReturn("Connexion impossible");
		$curlWrapper = new CurlWrapper($curlFunction);

		$this->assertFalse($curlWrapper->get("http://pastell.adullact.org"));
		$this->assertEquals("Erreur de connexion au serveur : Connexion impossible",$curlWrapper->getLastError());
	}


	public function testGet404(){
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_getinfo")->willReturn("404");
		$curlFunction->expects($this->any())->method("curl_exec")->willReturn("not found");
		$curlWrapper = new CurlWrapper($curlFunction);

		$this->assertFalse($curlWrapper->get("http://pastell.adullact.org"));
		$this->assertEquals("http://pastell.adullact.org : 404 Not Found",$curlWrapper->getLastError());
		$this->assertEquals("not found",$curlWrapper->getLastOutput());
	}

	public function testPost(){
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_exec")->willReturn("OK");

		$curlWrapper = new CurlWrapper($curlFunction);
		$curlWrapper->addPostData("foo","bar");

		$this->assertEquals("OK",$curlWrapper->get("http://pastell.adullact.org"));
	}

	public function testPostFile(){
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_exec")->willReturn("OK");

		$curlWrapper = new CurlWrapper($curlFunction);
		$curlWrapper->addPostFile("foo",__FILE__);

		$this->assertEquals("OK",$curlWrapper->get("http://pastell.adullact.org"));
	}

	public function testPostURLEncode(){
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_exec")->willReturn("OK");

		$curlWrapper = new CurlWrapper($curlFunction);
		$curlWrapper->setPostDataUrlEncode(array("foo"=>"bar"));

		$this->assertEquals("OK",$curlWrapper->get("http://pastell.adullact.org"));
	}

	public function testPostDataWithSimilarName(){
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_exec")->willReturn("OK");

		$curlWrapper = new CurlWrapper($curlFunction);
		$curlWrapper->addPostData("foo","bar");
		$curlWrapper->addPostData("foo","baz");

		$this->assertEquals("OK",$curlWrapper->get("http://pastell.adullact.org"));
	}

	public function testPostDataWithSimilarFilename(){
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_exec")->willReturn("OK");

		$curlWrapper = new CurlWrapper($curlFunction);
		$curlWrapper->addPostFile("foo",__FILE__,false,"text/plain","binary");
		$curlWrapper->addPostFile("foo",__DIR__."/fixtures/autorite-cert.pem");

		$this->assertEquals("OK",$curlWrapper->get("http://pastell.adullact.org"));
	}

	public function testGetHTTPCode(){
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_exec")->willReturn("OK");
		$curlFunction->expects($this->any())->method("curl_getinfo")->willReturn("403");
		$curlWrapper = new CurlWrapper($curlFunction);

		$this->assertEquals("OK",$curlWrapper->get("http://pastell.adullact.org"));
		$this->assertEquals("403",$curlWrapper->getHTTPCode());
	}

	public function testConstruct(){
		$curlWrapper = new CurlWrapper();
		$this->assertInstanceOf(CurlWrapper::class,$curlWrapper);
	}


	public function testsetJsonPostData(){
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_exec")->willReturn("OK");
		$curlFunction->expects($this->any())->method("curl_getinfo")->willReturn("200");

		$curlWrapper = new CurlWrapper($curlFunction);
		$curlWrapper->setJsonPostData(['foo'=>'bar','baz'=>'buz']);
		$curlFunction = $this->getMockBuilder("CurlFunctions")->getMock();
		$curlFunction->expects($this->any())->method("curl_exec")->willReturn("OK");
		$this->assertEquals("OK",$curlWrapper->get("http://pastell.adullact.org"));
	}


}