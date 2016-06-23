<?php

class APIActionTest extends PastellTestCase {

	/**
	 * @var APIAction
	 */
	private $apiAction;

	protected function setUp(){
		parent::setUp();
		$this->apiAction = new APIAction($this->getObjectInstancier(), 1);
	}


	public function testDetailConnecteurEntite(){
		$info = $this->apiAction->detailConnecteurEntite(1,11);
		$this->assertEquals("mailsec",$info['id_connecteur']);
	}


}