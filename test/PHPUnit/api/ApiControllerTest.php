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
	}

	public function testCallMethod(){
		$result = $this->apiController->callMethod('Version','info');
		$this->assertEquals('1.4-fixtures',$result['version']);
	}

	public function testCallBadController(){
		$this->setExpectedException("Exception","Impossible de trouver le controller NotExistingController");
		$this->apiController->callMethod('NotExistingController','notExistingMethod');
	}

	public function testCallBaMethod(){
		$this->setExpectedException("Exception","Impossible de trouver l'action Version::notExistingMethod");
		$this->apiController->callMethod('Version','notExistingMethod');
	}

	public function testCallJson(){
		$this->expectOutputRegex('#"version":"1.4-fixtures"#');
		$this->apiController->callJson('Version','info');
	}

	public function testCallJsonErrot(){
		$this->expectOutputRegex("#Impossible de trouver le controller NotExistingController#");
		$this->apiController->callJson("NotExistingController","NotExistingMethod");
	}

	public function testCallNotAuthenticated(){
		$apiAuthetication = $this->getMockBuilder('ApiAuthentication')->disableOriginalConstructor()->getMock();
		$apiAuthetication->expects($this->any())->method("getUtilisateurId")->willThrowException(new ApiAuthenticationException());

		$this->getObjectInstancier()->setInstance('ApiAuthentication',$apiAuthetication);

		$this->apiController = new ApiController($this->getObjectInstancier());

		$this->expectOutputRegex("#HTTP/1.1 401 Unauthorized#");
		$this->apiController->callJson('Version','info');

	}

}