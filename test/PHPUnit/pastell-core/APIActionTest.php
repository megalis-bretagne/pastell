<?php

require_once __DIR__.'/../init.php';


class APIActionTest extends PastellTestCase {

	/**
	 * @var APIAction
	 */
	private $apiAction;

	protected function setUp(){
		parent::setUp();
		$this->apiAction = new APIAction($this->getObjectInstancier(), 1);
	}

	public function reinitFileSystemOnSetup(){
		return true;
	}

	public function reinitDatabaseOnSetup(){
		return true;
	}

	public function testVersion(){
		$version = $this->apiAction->version();
		$this->assertEquals('1.4-fixtures', $version['version']);
	}

	public function testDetailDocument(){
		$info = $this->apiAction->createDocument(1,'mailsec');
		$result = $this->apiAction->detailDocument(1,$info['id_d']);
		$this->assertEquals('mailsec',$result['info']['type']);
	}

	public function testDetailConnecteurEntite(){
		$info = $this->apiAction->detailConnecteurEntite(1,11);
		$this->assertEquals("mailsec",$info['id_connecteur']);
	}

	public function  testDetailSeveralDocument(){
		$info = $this->apiAction->createDocument(1,'mailsec');
		$id_d_list[] = $info['id_d'];
		$info = $this->apiAction->createDocument(1,'mailsec');
		$id_d_list[] = $info['id_d'];
		$info = $this->apiAction->detailSeveralDocument(1,$id_d_list);
		$this->assertEquals('mailsec',$info[$id_d_list[1]]['info']['type']);
	}

}