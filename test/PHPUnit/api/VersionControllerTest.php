<?php


class VersionControllerTest extends PastellTestCase {

	public function testInfo(){
		/** @var VersionController $versionController */
		$versionController = $this->getObjectInstancier()->getInstance('VersionController');
		$info = $versionController->infoAction();
		$this->assertEquals('1.4-fixtures',$info['version']);
	}

}