<?php

require_once(PASTELL_PATH."/action/StandardAction.class.php");

class StandardActionTest extends PHPUnit\Framework\TestCase {

	/** @var  StandardAction */
	private $standardAction;

	protected function setUp(){
		$ymlLoader = new YMLLoader(new MemoryCacheNone());
		$type_definition = $ymlLoader->getArray(__DIR__."/fixtures/standard-action-definition.yml");

		$objectInstancier = new ObjectInstancier();

		$documentTypeFactory = $this
			->getMockBuilder("DocumentTypeFactory")
			->disableOriginalConstructor()
			->getMock();

		$documentTypeFactory
			->expects($this->any())
			->method("getFluxDocumentType")
			->willReturn(new DocumentType("test",$type_definition));
		$objectInstancier->{'DocumentTypeFactory'} = $documentTypeFactory;

		$connecteurTypeActionExecutor = $this->getMockForAbstractClass(
			"ConnecteurTypeActionExecutor",
			array($objectInstancier)
		);
		$connecteurTypeActionExecutor->expects($this->any())->method("go")->willReturn(true);

		$connecteurTypeFactory = $this->getMockBuilder('ConnecteurTypeFactory')
			->disableOriginalConstructor()
			->getMock();

		$map = array(
			array("signature","SignatureEnvoie",$connecteurTypeActionExecutor),
			array("signature","noExists",null)
		);

		$connecteurTypeFactory->expects($this->any())->method("getActionExecutor")->will($this->returnValueMap($map));
		$objectInstancier->{'ConnecteurTypeFactory'} = $connecteurTypeFactory;

		$this->standardAction = new StandardAction($objectInstancier);
		$this->standardAction->setAction("test");
	}

	public function testGo(){
		$this->assertTrue($this->standardAction->go());
	}

	public function testActionHasNoConnecteurType(){
		$this->standardAction->setAction("no-connecteur-type");
		$this->expectException(RecoverableException::class);
		$this->expectExceptionMessage("Aucun connecteur type n'a été défini pour l'action no-connecteur-type");
		$this->standardAction->go();
	}

	public function testActionHasNoActionExecutor(){
		$this->standardAction->setAction("no-action-class");
        $this->expectException(RecoverableException::class);
		$this->expectExceptionMessage("Impossible d'instancier une classe pour l'action : signature:noExists");
		$this->standardAction->go();
	}

	public function testNoMapping(){
		$this->standardAction->setAction("no-mapping");
		$this->assertTrue($this->standardAction->go());
	}

	public function testNoConnecteurTypeAction(){
		$this->standardAction->setAction("no-connecteur-type-action");
        $this->expectException(RecoverableException::class);
        $this->expectExceptionMessage("Aucune action n'a été défini pour l'action no-connecteur-type-action (connecteur-type : signature)");
		$this->standardAction->go();
	}

}