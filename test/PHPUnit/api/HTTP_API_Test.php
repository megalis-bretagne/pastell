<?php


class HTTP_API_Test extends PastellTestCase {

	/** @var  HTTP_API */
	private $http_api;

	protected function setUp(){
		parent::setUp();
		$apiAuthetication = $this->getMockBuilder('ApiAuthentication')->disableOriginalConstructor()->getMock();
		$apiAuthetication->expects($this->any())->method("getUtilisateurId")->willReturn(1);
		$this->getObjectInstancier()->setInstance('ApiAuthentication',$apiAuthetication);
	}

	private function getCall($ressource,$method = 'GET'){
		$this->http_api = new HTTP_API($this->getObjectInstancier());
		$this->http_api->setServerArray(array('REQUEST_METHOD'=>$method));
		$this->http_api->setGetArray(array(HTTP_API::PARAM_API_FUNCTION => "$ressource"));
		$this->http_api->dispatch();
	}

	public function testCallMethod(){
		$this->expectOutputRegex("#1.4-fixtures#");
		$this->getCall("/v2/version");
	}

	public function testNotFound(){
		$this->expectOutputRegex("#HTTP/1.1 404 Not Found#");
		$this->getCall("/v2/foo");
	}

	public function testCallBaMethod(){
		$this->expectOutputRegex("#HTTP/1.1 405 Method Not Allowed#");
		$this->getCall("/v2/version","PATCH");
	}

	public function testCallNotAuthenticated(){
		$apiAuthetication = $this->getMockBuilder('ApiAuthentication')->disableOriginalConstructor()->getMock();
		$apiAuthetication->expects($this->any())->method("getUtilisateurId")->willThrowException(new UnauthorizedException());
		$this->getObjectInstancier()->setInstance('ApiAuthentication',$apiAuthetication);

		$this->expectOutputRegex("#HTTP/1.1 401 Unauthorized#");
		$this->getCall("/v2/version");
	}


	public function testDispatchFailed(){
		$this->expectOutputRegex("#HTTP/1.1 400 Bad Request#");
		$this->getCall("/v2/");
	}

	public function testAPIV1(){
		$this->expectOutputRegex("#1.4-fixtures#");
		$this->getCall("/version.php");
	}

	public function testAPIV1NotFound(){
		$this->expectOutputRegex("#HTTP/1.1 404 Not Found#");
		$this->getCall("/foo.php");
	}

	public function testDispatchAllo(){
		$this->expectOutputRegex("#1.4-fixtures#");
		$this->getCall("rest/allo");
	}
}