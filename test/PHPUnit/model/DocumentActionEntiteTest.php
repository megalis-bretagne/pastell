<?php

class DocumentActionEntiteTest extends PastellTestCase {

	use DocumentTestCreator;

	/** @var DocumentActionEntite */
	private $documentActionEntite;

	protected function setUp() {
		parent::setUp();
		$this->documentActionEntite = $this->getObjectInstancier()->getInstance(DocumentActionEntite::class);
	}

	private function addAction($id_d,$action_name){
		$action = new DocumentActionSQL($this->getSQLQuery());
		$id_a = $action->add($id_d,1,1,$action_name);
		$this->documentActionEntite->add($id_a,1,false);
	}

	/**
	 * @throws Exception
	 */
	public function testGetLastAction(){
		$id_d = $this->createDocument();
		$this->addAction($id_d,"action-test");

		$this->assertEquals(
			"action-test",
			$this->documentActionEntite->getLastAction(1,$id_d)
		);
	}

	/**
	 * @throws Exception
	 */
	public function testGetLastActionNotModif(){
		$id_d = $this->createDocument();
		$this->addAction($id_d,"action-test");
		$this->addAction($id_d,"modification");

		$this->assertEquals(
			"action-test",
			$this->documentActionEntite->getLastActionNotModif(1,$id_d)
		);
		$this->assertEquals(
			"modification",
			$this->documentActionEntite->getLastAction(1,$id_d)
		);
	}

	/**
	 * @throws Exception
	 */
	public function testGetDocumentOlderThanDay(){
		$id_d = $this->createDocument();
		$this->addAction($id_d,"action-test");
		$this->addAction($id_d,"modification");
		$documents_list = $this->documentActionEntite->getDocumentOlderThanDay(
			1,
			"document-type-test",
			"action-test",
			0
		);
		$this->assertEquals([],$documents_list);
	}

	/**
	 * @throws Exception
	 */
	public function testGetDocumentInStateOlderThanDay(){
		$id_d = $this->createDocument();
		$this->addAction($id_d,"action-test");
		$this->addAction($id_d,"modification");
		$documents_list = $this->documentActionEntite->getDocumentInStateOlderThanDay(
			1,
			"document-type-test",
			"action-test",
			0
		);
		$this->assertEquals("modification",$documents_list[0]['last_action']);
	}

}

