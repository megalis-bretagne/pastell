<?php

class ConnecteurDefinitionFilesTest extends PastellTestCase {

	/** @var  ConnecteurDefinitionFiles */
	private $connecteurDefinitionFiles;

	protected function setUp() {
		parent::setUp();
		$this->connecteurDefinitionFiles =
			$this->getObjectInstancier()->getInstance("ConnecteurDefinitionFiles");
	}

	public function testGetAllType(){
		$result = $this->connecteurDefinitionFiles->getAllType();
		$this->assertContains("mailsec",$result);
	}

	public function testGetAllTypeTwoConnecteur(){
		$this->getInternalAPI()->post(
			"/Extension/",
				array('path'=>__DIR__.'/../fixtures/extensions/extension-test')
		);
		$result = $this->connecteurDefinitionFiles->getAllType();
		$this->assertEquals(1,array_count_values($result)['test']);
	}


}