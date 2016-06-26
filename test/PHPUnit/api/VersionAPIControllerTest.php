<?php


class VersionAPIControllerTest extends PastellTestCase {

	public function testInfo(){
		/** @var VersionAPIController $versionController */
		$versionController = $this->getAPIController('Version',1);
		$info = $versionController->infoAction();
		$this->assertEquals('1.4-fixtures',$info['version']);
	}

}