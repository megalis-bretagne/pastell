<?php


class ApiControllerTest extends PastellTestCase {

	/** @var  ApiController */
	private $apiController;

	protected function setUp(){
		parent::setUp();

		$apiAuthetication = $this->getMockBuilder('ApiAuthentication')->disableOriginalConstructor()->getMock();
		$apiAuthetication->expects($this->any())->method("getUtilisateurId")->willReturn(1);
		
		$this->getObjectInstancier()->setInstance('ApiAuthentication',$apiAuthetication);
		
		$this->apiController = new ApiController($this->getObjectInstancier());
		$this->apiController->setServerArray(array('REQUEST_METHOD'=>'GET'));

	}

	public function testCallMethod(){
		$result = $this->apiController->callMethod('Version','info','GET');
		$this->assertEquals('1.4-fixtures',$result['version']);
	}

	public function testCallBadController(){
		$this->setExpectedException("Exception","Impossible de trouver le controller NotExistingController");
		$this->apiController->callMethod('NotExistingController','notExistingMethod','GET');
	}

	public function testCallBaMethod(){
		//$this->apiController->setServerArray(array('REQUEST_METHOD'=>'PATCH'));
		//$this->expectOutputRegex("#HTTP/1.1 405 Method Not Allowed#");
		$this->setExpectedException("Exception","La méthode PATCH n'est pas disponible pour l'objet Version");
		$this->apiController->callMethod('Version','method','PATCH');
	}

	public function testCallJson(){
		$this->expectOutputRegex('#"version": "1.4-fixtures"#');
		$this->apiController->callJson('Version','info','GET');
	}

	public function testCallJsonErrot(){
		$this->expectOutputRegex("#Impossible de trouver le controller NotExistingController#");
		$this->apiController->callJson("NotExistingController","NotExistingMethod",'GET');
	}

	public function testCallNotAuthenticated(){
		$apiAuthetication = $this->getMockBuilder('ApiAuthentication')->disableOriginalConstructor()->getMock();
		$apiAuthetication->expects($this->any())->method("getUtilisateurId")->willThrowException(new ApiAuthenticationException());

		$this->getObjectInstancier()->setInstance('ApiAuthentication',$apiAuthetication);

		$this->apiController = new ApiController($this->getObjectInstancier());

		$this->expectOutputRegex("#HTTP/1.1 401 Unauthorized#");
		$this->apiController->callJson('Version','info','GET');
	}

	public function testDispatch(){
		$this->apiController->setGetArray(array('api_function'=>'version.php'));
		$this->expectOutputRegex("#1.4-fixtures#");
		$this->apiController->dispatch();
	}

	public function testDispatchFailed(){
		$this->expectOutputRegex("#Il faut sp.*cifier une fonction de l'api#");
		$this->apiController->dispatch();
	}

	public function testDispatchNotFound(){
		$this->apiController->setGetArray(array('api_function'=>'foo.php'));
		$this->expectOutputRegex("#Impossible de trouver le script foo.php#");
		$this->apiController->dispatch();
	}

	public function testDispatchAllo(){
		$this->apiController->setGetArray(array('api_function'=>'rest/allo'));
		$this->expectOutputRegex("#1.4-fixtures#");
		$this->apiController->dispatch();
	}

	public function testVersionPostNotAllowed(){
		//$this->expectOutputRegex("#HTTP/1.1 405 Method Not Allowed#");
		$this->setExpectedException("Exception","La méthode POST n'est pas disponible pour l'objet version");
		$this->apiController->callMethod('Version','aa','POST');
	}

}