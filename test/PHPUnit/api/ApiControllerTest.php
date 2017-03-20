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
		$result = $this->apiController->callMethod('Version',array('info'),'GET');
		$this->assertEquals('1.4-fixtures',$result['version']);
	}

	public function testCallBadController(){
		$this->setExpectedException("Exception","La ressource NotExistingController n'a pas été trouvée");
		$this->apiController->callMethod('NotExistingController',array('notExistingMethod'),'GET');
	}

	public function testCallBaMethod(){
		//$this->apiController->setServerArray(array('REQUEST_METHOD'=>'PATCH'));
		//$this->expectOutputRegex("#HTTP/1.1 405 Method Not Allowed#");
		$this->setExpectedException("Exception","La méthode PATCH n'est pas disponible pour l'objet Version");
		$this->apiController->callMethod('Version',array(),'PATCH');
	}

	public function testCallJson(){
		$this->expectOutputRegex('#"version": "1.4-fixtures"#');
		$this->apiController->callJson('version');
	}

	public function testCallJsonError(){
		$this->setExpectedException("NotFoundException","La ressource NotExistingController n'a pas été trouvée");
		$this->apiController->callJson("NotExistingController",array("NotExistingMethod"),'GET');
	}

	public function testCallNotAuthenticated(){
		$apiAuthetication = $this->getMockBuilder('ApiAuthentication')->disableOriginalConstructor()->getMock();
		$apiAuthetication->expects($this->any())->method("getUtilisateurId")->willThrowException(new UnauthorizedException());

		$this->getObjectInstancier()->setInstance('ApiAuthentication',$apiAuthetication);

		$this->apiController = new ApiController($this->getObjectInstancier());

		//$this->expectOutputRegex("#HTTP/1.1 401 Unauthorized#");
		$this->setExpectedException("UnauthorizedException");
		$this->apiController->callJson('version');
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
		$this->apiController->callMethod('Version',array('aa'),'POST');
	}

}