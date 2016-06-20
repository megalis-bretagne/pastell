<?php

class ConnecteurTypeFactoryTest extends PHPUnit_Framework_TestCase {

	/** @var  ConnecteurTypeFactory */
	private $connecteurTypeFactory;

	protected function setUp(){
		$extensions = $this->getMockBuilder("Extensions")->disableOriginalConstructor()->getMock();
		$extensions
			->expects($this->any())
			->method("getAllConnecteurType")
			->willReturn(array("signature"=>__DIR__."/fixtures/"));

		$objectInstancier  = new ObjectInstancier();
		$objectInstancier->{'Extensions'} = $extensions;
		$this->connecteurTypeFactory = new ConnecteurTypeFactory($objectInstancier);
	}

	public function testGetActionExecutor(){
		$this->assertTrue($this->connecteurTypeFactory->getActionExecutor("signature","SignatureEnvoieMock")->go());
	}

	public function testConnecteurTypeNotFound(){
		$this->setExpectedException("RecoverableException","Impossible de trouver le connecteur type sae");
		$this->connecteurTypeFactory->getActionExecutor("sae","SignatureEnvoieMock")->go();
	}

	public function testClassNotFound(){
		$this->setExpectedExceptionRegExp("RecoverableException","#Le fichier .*NotFoundMock.class.php n'a pas été trouvé#");
		$this->connecteurTypeFactory->getActionExecutor("signature","NotFoundMock")->go();
	}

	public function testGetAllActionExecutor(){
		$this->assertEquals(array("SignatureEnvoieMock"),$this->connecteurTypeFactory->getAllActionExecutor());
	}

}